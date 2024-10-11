<?php

namespace App\Repositories\OfficeRoom;

use App\OfficeRoom;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class OfficeRoomRepository implements OfficeRoomRepositoryInterface
{
    /**
     * @var array
     */
    protected $filterData;

    /**
     * @var Builder
     */
    protected $searchQuery;

    /**
     * @param array $data
     * @return OfficeRoomRepository
     */
    protected function setFilterData(array $data): OfficeRoomRepository
    {
        $this->filterData = $data;

        return $this;
    }

    /**
     * @return OfficeRoomRepository
     */
    public function setSearchQuery(): OfficeRoomRepository
    {
        $this->searchQuery = OfficeRoom::query()
            ->select([
                'office_rooms.id as id',
                'office_rooms.name as name',
            ]);

        return $this;
    }

    /**
     * @param array $data
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection|Collection
     */
    public function getAllFree(array $data): Collection
    {
        $this->setFilterData($data)
            ->setSearchQuery()
            ->applyFilters()
            ->setSort();


        return $this->searchQuery->whereNotNull('external_id')->get();
    }

    /**
     * @return OfficeRoomRepository
     */
    protected function applyFilters(): OfficeRoomRepository
    {
        $this->applyOfficeFilter()
            ->applyDateFilter();

        return $this;
    }

    /**
     * @return OfficeRoomRepository
     */
    protected function applyOfficeFilter(): OfficeRoomRepository
    {
        if (!empty($this->filterData['office_id'])) {
            $this->searchQuery->whereHas('office', function ($query) {
                $query->where('id', $this->filterData['office_id']);
            });
        }

        return $this;
    }

    /**
     * @return OfficeRoomRepository
     */
    protected function setSort(): OfficeRoomRepository
    {
        $this->searchQuery->orderBy('name');

        return $this;
    }

    /**
     * @return OfficeRoomRepository
     */
    protected function applyDateFilter(): OfficeRoomRepository
    {
        if (!empty($this->filterData['from']) && !empty($this->filterData['to'])) {
            $this->searchQuery->whereNotIn('id', function ($query) {
                $query->select('office_room_id')
                    ->from('appointments')
                    ->whereRaw('(appointments.time + (appointments.visit_length * 60)) > ' . Carbon::parse($this->filterData['from'])->timestamp)
                    ->where('appointments.time', '<', Carbon::parse($this->filterData['to'])->timestamp)
                    ->groupBy('office_room_id');
            });
        }

        return $this;
    }
}