<?php

/**
 * Invoice Ninja (https://invoiceninja.com).
 *
 * @link https://github.com/invoiceninja/invoiceninja source repository
 *
 * @copyright Copyright (c) 2025. Invoice Ninja LLC (https://invoiceninja.com)
 *
 * @license https://www.elastic.co/licensing/elastic-license
 */

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Invoice;
use App\Models\Project;
use Illuminate\Http\Response;
use App\Factory\ProjectFactory;
use App\Filters\ProjectFilters;
use App\Utils\Traits\MakesHash;
use App\Utils\Traits\SavesDocuments;
use App\Utils\Traits\GeneratesCounter;
use App\Repositories\ProjectRepository;
use App\Transformers\ProjectTransformer;
use App\Services\Template\TemplateAction;
use App\Http\Requests\Project\BulkProjectRequest;
use App\Http\Requests\Project\EditProjectRequest;
use App\Http\Requests\Project\ShowProjectRequest;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\CreateProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Requests\Project\UploadProjectRequest;
use App\Http\Requests\Project\DestroyProjectRequest;
use App\Http\Requests\Project\InvoiceProjectRequest;
use App\Transformers\InvoiceTransformer;

/**
 * Class ProjectController.
 */
class ProjectController extends BaseController
{
    use MakesHash;
    use SavesDocuments;
    use GeneratesCounter;

    protected $entity_type = Project::class;

    protected $entity_transformer = ProjectTransformer::class;

    protected $project_repo;

    /**
     * ProjectController constructor.
     * @param ProjectRepository $project_repo
     */
    public function __construct(ProjectRepository $project_repo)
    {
        parent::__construct();

        $this->project_repo = $project_repo;
    }
    public function index(ProjectFilters $filters)
    {
        $projects = Project::filter($filters);

        return $this->listResponse($projects);
    }

    public function show(ShowProjectRequest $request, Project $project)
    {
        return $this->itemResponse($project);
    }

    public function edit(EditProjectRequest $request, Project $project)
    {
        return $this->itemResponse($project);
    }

    public function update(UpdateProjectRequest $request, Project $project)
    {
        if ($request->entityIsDeleted($project)) {
            return $request->disallowUpdate();
        }

        $project->fill($request->all());
        $project->number = empty($project->number) ? $this->getNextProjectNumber($project) : $project->number;
        $project->saveQuietly();

        if ($request->has('documents')) {
            $this->saveDocuments($request->input('documents'), $project, $request->input('is_public', true));
        }

        event('eloquent.updated: App\Models\Project', $project);

        return $this->itemResponse($project->fresh());
    }
    public function create(CreateProjectRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $project = ProjectFactory::create($user->company()->id, $user->id);

        return $this->itemResponse($project);
    }
    public function store(StoreProjectRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $project = ProjectFactory::create($user->company()->id, $user->id);
        $project->fill($request->all());
        $project->saveQuietly();

        if (empty($project->number)) {
            $project->number = $this->getNextProjectNumber($project);
            $project->saveQuietly();
        }

        if ($request->has('documents')) {
            $this->saveDocuments($request->input('documents'), $project, $request->input('is_public', true));
        }

        event('eloquent.created: App\Models\Project', $project);

        return $this->itemResponse($project->fresh());
    }

    public function destroy(DestroyProjectRequest $request, Project $project)
    {
        //may not need these destroy routes as we are using actions to 'archive/delete'
        $project->is_deleted = true;
        $project->delete();
        $project->save();

        return $this->itemResponse($project->fresh());
    }
    public function bulk(BulkProjectRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $action = $request->input('action');

        $ids = $request->input('ids');

        $projects = Project::withTrashed()->whereIn('id', $this->transformKeys($ids))->company()->get();

        if ($action == 'invoice' && $user->can('edit', $projects->first())) {
            $invoice = $this->project_repo->invoice($projects);
            $this->entity_transformer = InvoiceTransformer::class;
            $this->entity_type = Invoice::class;
            return $this->itemResponse($invoice);
        }

        if ($action == 'template' && $user->can('view', $projects->first())) {

            $hash_or_response = $request->boolean('send_email') ? 'email sent' : \Illuminate\Support\Str::uuid();

            TemplateAction::dispatch(
                $projects->pluck('hashed_id')->toArray(),
                $request->template_id,
                Project::class,
                $user->id,
                $user->company(),
                $user->company()->db,
                $hash_or_response,
                $request->boolean('send_email')
            );

            return response()->json(['message' => $hash_or_response], 200);
        }

        $projects->each(function ($project) use ($action, $user) {
            if ($user->can('edit', $project)) {
                $this->project_repo->{$action}($project);
            }
        });

        return $this->listResponse(Project::withTrashed()->whereIn('id', $this->transformKeys($ids)));
    }

    public function upload(UploadProjectRequest $request, Project $project)
    {
        if (! $this->checkFeature(Account::FEATURE_DOCUMENTS)) {
            return $this->featureFailure();
        }

        if ($request->has('documents')) {
            $this->saveDocuments($request->file('documents'), $project, $request->input('is_public', true));
        }

        return $this->itemResponse($project->fresh());
    }

    public function invoice(InvoiceProjectRequest $request, Project $project)
    {
        $this->entity_transformer = InvoiceTransformer::class;
        $this->entity_type = Invoice::class;

        $invoice = $this->project_repo->invoice($project);

        return $this->itemResponse($invoice);
    }
}
