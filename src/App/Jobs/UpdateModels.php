<?php

namespace Asseco\JsonSearch\App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateModels implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected string $model,
        protected array $ids,
        protected array $updateProperties
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $models = $this->model::query()->whereIn('id', $this->ids)->get();

        $this->model::query()->whereIn('id', $this->ids)->update($this->updateProperties);

        foreach ($models as $model) {
            $model->fill($this->updateProperties);
            event('eloquent.updated: ' . get_class($model), $model);
            event('eloquent.saved: ' . get_class($model), $model);
        }
    }
}
