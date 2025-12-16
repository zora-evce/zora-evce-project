<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class Breadcrumbs
{
    public static function generate(Request $request): array
    {
        $routeName = optional($request->route())->getName();

        $map = [
            'cpo.dashboard' => ['Dashboard'],
            'cpo.stations' => ['Stations'],
            'cpo.stations.details' => ['Stations', 'Details'],
            'cpo.transactions.chargepoints' => ['Transactions', 'Chargepoints'],
            'cpo.master.tariff' => ['Master', 'Tariff'],
            'cpo.users' => ['Users'],
            'cpo.users.create' => ['Users', 'Create'],
            'cpo.users.edit' => ['Users', 'Edit'],
            'cpo.users.detail' => ['Users', 'Details'],
            'cpo.my-account' => ['My Account'],
        ];

        if ($routeName && isset($map[$routeName])) {
            return self::fromTitles($map[$routeName]);
        }

        return self::fromSegments($request);
    }

    protected static function fromTitles(array $titles): array
    {
        $items = [];
        foreach ($titles as $i => $title) {
            $items[] = [
                'title' => $title,
                'url' => $i === count($titles) - 1 ? null : null,
            ];
        }

        return self::prependDashboard($items);
    }

    protected static function fromSegments(Request $request): array
    {
        $segments = collect($request->segments())
            ->reject(fn ($s) => is_numeric($s))
            ->values();

        $items = [];
        $path = '';

        foreach ($segments as $i => $seg) {
            $path .= '/' . $seg;
            $items[] = [
                'title' => Str::of($seg)->replace('-', ' ')->title(),
                'url' => $i === $segments->count() - 1 ? null : url($path),
            ];
        }

        return self::prependDashboard($items);
    }

    protected static function prependDashboard(array $items): array
    {
        array_unshift($items, [
            'title' => 'Dashboard',
            'url' => route('cpo.dashboard'),
        ]);

        if (count($items) === 1) {
            $items[0]['url'] = null;
        }

        return $items;
    }
}
