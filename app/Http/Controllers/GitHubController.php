<?php

namespace App\Http\Controllers;

use App\GitHubApp\GitHubAppRepo;
use App\GitHubApp\GitHubApp;
use App\GitHubApp\Payload\BranchPayload;
use App\GitHubApp\Payload\Payload;
use App\GitHubApp\Payload\PrPayload;
use App\Jobs\BranchUpJob;
use App\Jobs\BranchDownJob;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class GitHubController extends Controller
{

    public function setup(Request $request)
    {
        \Log::info($request);
    }

    public function webhook(Request $request)
    {
        $type = $request->header('x-github-event');
        $content = $request->getContent();

        \Log::debug($type . ' => ' . $content);

        switch ($type) {
            case 'pull_request':
                $this->pullRequest(PrPayload::make($content));
                break;
            case 'push':
                break;
            case 'installation':
            case 'installation_repositories':
            case 'integration_installation_repositories':
                $this->integrationInstallationRepositories(Payload::make($content));
                break;
            default:

        }
    }

    private function integrationInstallationRepositories(Payload $payload)
    {
        $app = new GitHubApp();
        if(! $app->data()->hasInstallation()) {
            $app->data()->foreverInstallation((array)$payload);
        }
    }

    private function pullRequest(PrPayload $payload)
    {
        $action = Arr::get($payload, 'action');
        $pr = PrPayload::make(Arr::get($payload, 'pull_request'));

        $repo = $pr->baseRepoName();

        if($action == 'opened' || $action == 'reopened') {
            $app = new GitHubApp();
            $app->data()->forceBranches($pr->headRepoName());
            $app->data()->forcePrs($pr->headRepoName());
        }

        if ($action == 'opened' || $action == 'synchronize' || $action == 'reopened') {
            $this->dispatch(new BranchUpJob(
                $repo,
                $pr->headRef(),
                $pr->headSha(),
                $pr->baseRef()
            ));
        } else if ($action == 'closed') {
            $this->dispatch(new BranchDownJob(
                $repo,
                $pr->headRef()
            ));
        }
    }
}
