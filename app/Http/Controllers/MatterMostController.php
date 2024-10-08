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
        $title = $requestData['status'] === 'success' ? 'Success: ' : 'Failed: ';
        $title .= $requestData['server']['name'] . ' - ' . $requestData['site']['name'];

        $serverUrl = 'https://forge.laravel.com/servers/' . $requestData['server']['id'];
        $siteUrl = $serverUrl . '/sites/' . $requestData['site']['id'];

        return [
            'username' => 'Forge',
            'icon_url' => 'https://forge.laravel.com/favicon.ico',
            'channel' => $channelName,
            'attachments' => [
                [
                    'fallback' => $title,
                    'color' => $requestData['status'] === 'success' ? 'good' : 'danger',
                    'pretext' => $title,
                    'author_name' => $requestData['commit_author'],
                    'author_link' => $requestData['commit_url'],
                    'author_icon' => 'https://github.githubassets.com/favicon.ico',
                    'title' => 'Deployed to ' . $requestData['site']['name'],
                    'title_link' => $requestData['commit_url'],
                    'text' => $requestData['commit_message'],
                    'fields' => [
                        [
                            'title' => 'Server',
                            'value' => '[' . $requestData['server']['name'] . '](' . $serverUrl . ')',
                            'short' => true,
                        ],
                        [
                            'title' => 'Site',
                            'value' => '[' . $requestData['site']['name'] . '](' . $siteUrl . ')',
                            'short' => true,
                        ],
                        [
                            'title' => 'Site URL',
                            'value' => '[https://' . $requestData['site']['name'] . '](https://' . $requestData['site']['name'] . ')',
                            'short' => true,
                        ],
                        [
                            'title' => 'Commit hash',
                            'value' => $requestData['commit_hash'],
                            'short' => true,
                        ],
                    ],
                    'footer' => 'Laravel Forge Deployment',
                    'footer_icon' => 'https://forge.laravel.com/favicon.ico',
                    'ts' => now()->timestamp,
                ],
            ],
        ];
    }
}
