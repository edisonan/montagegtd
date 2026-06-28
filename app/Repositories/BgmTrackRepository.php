<?php

namespace App\Repositories;

use App\Models\BgmTrack;

class BgmTrackRepository
{
    public function getActiveTracks($limit = 30, $keyword = null)
    {
        $query = BgmTrack::where('is_active', 1)
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc');

        if (!empty($keyword)) {
            $query->where(function ($innerQuery) use ($keyword) {
                $innerQuery->where('search_keyword', $keyword)
                    ->orWhere('title', 'like', '%' . $keyword . '%')
                    ->orWhere('artist', 'like', '%' . $keyword . '%');
            });
        }

        return $query->limit($limit)->get();
    }
}
