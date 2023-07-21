<?php

namespace App\Http\Controllers;

use App\Http\Requests\MatterMostProxyRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class MatterMostController extends Controller
{
    public function proxy(MatterMostProxyRequest $request, string $hookId, string $channelName): Response
    {
        $host = config('services.mattermost.host');
        $url = "{$host}/hooks/{$hookId}";

        return Http::post($url, $this->getPayload($request->validated(), $channelName));
    }

    /**
     * @param array $requestData
     * @param string $channelName
     * @return array
     */
    private function getPayload(array $requestData, string $channelName): array
    {
        return [
            'channel' => $channelName,
            'attachments' => [
                [
                    'fallback' => $requestData['status'] === 'success' ? 'New deployment' : 'Deployment failed',
                    'color' => $requestData['status'] === 'success' ? 'good' : 'danger',
                    'pretext' => $requestData['status'] === 'success' ? 'New deployment' : 'Deployment failed',
                    'author_name' => $requestData['commit_author'],
                    'author_link' => $requestData['commit_url'],
                    'author_icon' => 'https://github.githubassets.com/favicon.ico',
                    'title' => $requestData['commit_message'],
                    'title_link' => $requestData['commit_url'],
                    'text' => 'Deployed to ' . $requestData['site']['name'],
                    'fields' => [
                        [
                            'title' => 'Server',
                            'value' => $requestData['server']['name'],
                            'short' => true,
                        ],
                        [
                            'title' => 'Site',
                            'value' => $requestData['site']['name'],
                            'short' => true,
                        ],
                        [
                            'title' => 'Commit hash',
                            'value' => $requestData['commit_hash'],
                            'short' => true,
                        ],
                    ],
                    'footer' => 'Laravel Forge Deployment',
                    'footer_icon' => 'https://forge.laravel.com/favicon-16x16.png',
                    'ts' => now()->timestamp,
                ],
            ],
        ];
    }
}
