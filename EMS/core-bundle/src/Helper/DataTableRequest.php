<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Helper;

use Symfony\Component\HttpFoundation\Request;

final readonly class DataTableRequest
{
    private function __construct(private int $draw, private int $from, private int $size, private ?string $orderField, private string $orderDirection, private string $searchValue)
    {
    }

    public static function fromRequest(Request $request): self
    {
        $from = (int) $request->get('start', 0);
        $size = (int) $request->get('length', 10);
        $order = $request->get('order', []);
        if (!\is_array($order)) {
            throw new \RuntimeException('Unexpected non array request parameter');
        }
        $columns = $request->get('columns', []);
        if (!\is_array($columns)) {
            throw new \RuntimeException('Unexpected non array request parameter');
        }
        $orderDirection = (string) ($order[0]['dir'] ?? 'asc');
        $orderColumn = (int) ($order[0]['column'] ?? 0);
        $orderField = null;

        /** @var array{name?: ?string, orderable?: string} $columnOrder */
        $columnOrder = $columns[$orderColumn] ?? null;
        $columnOrderName = $columnOrder['name'] ?? null;
        $columnOrderOrderable = 'true' === ($columnOrder['orderable'] ?? 'false');

        if ($columnOrderName && $columnOrderOrderable) {
            $orderField = (string) $columnOrderName;
        }

        $search = $request->get('search', []);
        if (!\is_array($search)) {
            throw new \RuntimeException('Unexpected non array request parameter');
        }
        $searchValue = (string) ($search['value'] ?? '');

        $draw = (int) $request->get('draw', 0);

        return new DataTableRequest($draw, $from, $size, $orderField, $orderDirection, $searchValue);
    }

    public function getDraw(): int
    {
        return $this->draw;
    }

    public function getFrom(): int
    {
        return $this->from;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getOrderField(): ?string
    {
        return $this->orderField;
    }

    public function getOrderDirection(): string
    {
        return $this->orderDirection;
    }

    public function getSearchValue(): string
    {
        return $this->searchValue;
    }
}
