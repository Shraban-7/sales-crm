<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\ActivityType;
use App\Models\Activity;
use App\Models\Attachment;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Event;
use App\Models\FollowUp;
use App\Models\ImportExportJob;
use App\Models\Lead;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\Proposal;
use App\Models\Quotation;
use App\Models\Reminder;
use App\Models\Target;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use App\Models\Webhook;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Model::shouldBeStrict(! $this->app->isProduction());

        Relation::enforceMorphMap([
            'user' => User::class,
            'team' => Team::class,
            'company' => Company::class,
            'contact' => Contact::class,
            'lead' => Lead::class,
            'pipeline' => Pipeline::class,
            'pipeline_stage' => PipelineStage::class,
            'deal' => Deal::class,
            'activity' => Activity::class,
            'attachment' => Attachment::class,
            'task' => Task::class,
            'reminder' => Reminder::class,
            'follow_up' => FollowUp::class,
            'quotation' => Quotation::class,
            'proposal' => Proposal::class,
            'event' => Event::class,
            'target' => Target::class,
            'proposal' => \App\Models\Proposal::class,
            'event' => \App\Models\Event::class,
            'target' => \App\Models\Target::class,
            'import_export_job' => ImportExportJob::class,
            'webhook' => Webhook::class,
        ]);

        Password::defaults(function (): Password {
            $rule = Password::min(12)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols();

            return $this->app->isProduction() ? $rule->uncompromised() : $rule;
        });

        Gate::before(function (User $user, string $ability, array $arguments = []): ?bool {
            if (! $user->hasRole('Super Admin')) {
                return null;
            }

            if (
                isset($arguments[0])
                && $arguments[0] instanceof Lead
                && $arguments[0]->isConverted()
                && in_array($ability, ['update', 'convert'], true)
            ) {
                return false;
            }

            if (
                isset($arguments[0])
                && $arguments[0] instanceof Quotation
                && ! $arguments[0]->isDraft()
                && in_array($ability, ['update', 'delete'], true)
            ) {
                return false;
            }

            if (
                isset($arguments[0])
                && $arguments[0] instanceof Activity
                && $arguments[0]->type === ActivityType::System
                && in_array($ability, ['update', 'delete'], true)
            ) {
                return false;
            }

            return true;
        });
    }
}
