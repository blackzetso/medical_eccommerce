<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Determine the root view based on the request URL.
     */
    public function rootView(Request $request): string
    {
        if (str_starts_with($request->path(), 'admin')) {
            return 'admin';
        }

        return parent::rootView($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $shared = parent::share($request);

        return array_merge($shared, [
            'auth' => array_merge($shared['auth'] ?? [], [
                'user' => function () use ($request) {
                    if ($request->user()) {
                        return [
                            'id' => $request->user()->id,
                            'name' => $request->user()->name,
                            'email' => $request->user()->email,
                            'user_type' => $request->user()->user_type ?? 'client',
                        ];
                    }
                    return null;
                },
                'roles' => function () use ($request) {
                    if (!$request->user()) {
                        return [];
                    }
                    $roles = $request->user()->getRoleNames();
                    return $roles ? $roles->toArray() : [];
                },
                'permissions' => function () use ($request) {
                    if (!$request->user()) {
                        return [];
                    }
                    try {
                        $permissions = $request->user()->getAllPermissions();
                        $permissionNames = $permissions->pluck('name');
                        return $permissionNames ? $permissionNames->toArray() : [];
                    } catch (\Exception $e) {
                        \Log::error('Error getting permissions: ' . $e->getMessage());
                        return [];
                    }
                },
            ]),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
            ],
            'footerSettings' => fn () => $this->getFooterSettings($request),
        ]);
    }

    /**
     * Get footer settings for frontend pages only
     *
     * @param Request $request
     * @return array|null
     */
    private function getFooterSettings(Request $request): ?array
    {
        // Only share footer settings for frontend pages (not admin)
        if (str_starts_with($request->path(), 'admin')) {
            return null;
        }

        try {
            $footerFeatures = Setting::get('footer_features', '');
            $featuresList = !empty($footerFeatures) ? explode("\n", $footerFeatures) : [];
            
            return [
                'address' => Setting::get('site_address', ''),
                'phone' => Setting::get('site_phone', ''),
                'email' => Setting::get('site_email', ''),
                'working_hours' => Setting::get('site_working_hours', ''),
                'footer_features' => $featuresList,
                'facebook_url' => Setting::get('footer_facebook_url', '#'),
                'twitter_url' => Setting::get('footer_twitter_url', '#'),
                'instagram_url' => Setting::get('footer_instagram_url', '#'),
            ];
        } catch (\Exception $e) {
            // If settings table doesn't exist or has issues, return defaults
            return [
                'address' => '',
                'phone' => '',
                'email' => '',
                'working_hours' => '',
                'footer_features' => [],
                'facebook_url' => '#',
                'twitter_url' => '#',
                'instagram_url' => '#',
            ];
        }
    }

}
