<?php

declare(strict_types=1);

namespace Asseco\JsonSearch\App\Http\Resources;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class PaginatedResponse
{
    public function __construct(
        private Collection|array $data,
        private int              $totalCount,
        private Request          $request
    )
    {}

    public function toArray(): array
    {
        $data = $this->data instanceof Collection ? $this->data : collect($this->data);

        return [
            'data' => $data,
            'metadata' => [
                'page'          => $this->request->input('page', 1),
                'limit'         => $this->request->input('limit'),
                'count'         => $data->count(),
                'total_count'   => $this->totalCount,
            ],
        ];
    }
}
