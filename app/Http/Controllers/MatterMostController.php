<?php

namespace App\Http\Controllers;

use App\Http\Requests\MatterMostProxyRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class MatterMostController extends Controller
{
    public function proxy(MatterMostProxyRequest $request, string $hookId, string $channelName): Response
    {
        $host = config('services.mattermost.host');
        $url = "{$host}/hooks/{$hookId}";

        return Http::post($url, $this->getPayload($request->validated(), $channelName));
    }

    public function nasProxy(Request $request, string $hookId, string $channelName): Response
    {
        $host = config('services.mattermost.host');
        $url = "{$host}/hooks/{$hookId}";

        Log::info('Mattermost request', [
            'url' => $url,
            'payload' => $request->all(),
        ]);

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
                    'footer_icon' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAAAXNSR0IArs4c6QAAAqVJREFUWEftlk1IVFEUx3/nzYxGmBVFYYVFbWzTKggXBWOQ48fCWZS4CdRRKcJNG2uR9ElRUBQRk19BtmgiJCUdDRQialGt26RQgrQJrBQcZ+adeoqU+mbeNM7gxru99/7/v3vOuedeYZWHrLI/awDLI9DfVoIhNYhkLzqKgegY5NxebDLS6iZS+AT0RNZrQ+Qmb7+2LAZ4FSwk7hoG9mUZ4CNi+CmtG18MMPBgD+K5A+wHtgP5kPFCnUappSzwzDrk8jyPdK0jMrsZlV243YeIazVoMeDKTFSkC3P6FOXNEXuApS59wa143PeT1EUE5D0wCZq4cAVB5Qcx8zKVDZ8WbFKr9HDHSdBH9umQN0RjfvImJtmwI7nerwnFezH27xlTAxjsPIea12xTYMgNjtW3pJseZ4APQQ/fXSGUKhuTWdQ8Tlljb/YAXrbvxs0wyl4bk3HMuJfyptHsAYTbK4DnQK6NyRQiYZQpZEkBqihx7aIi8DoZnHMKwh1XQc+nccIoalZT1tiTPkBfcD0el5Xfo2kAjAMl+AKf0wcYDBahc6254P8BZJBcowpv7Uz6AEOdNZjm4yRd0EwgbrWdC5QGrjiBJ6+BcPs94Iy9iPSi2m0/Z8YwPe+oqP2WPkB/dz7GTBiw3oGlY3auNfsaXjgZOM0njkC47TBinZJNNiJfiEkJlfVjTgZO8/YA4WABuB+CVtoKiAwQn/YvvGhOJqkVYf/dXFx529BYMeI6jeqRxH8BoxVf3aWVGC/snY/A/HW7DhwAdgI5ScSta+XHF7DqY8VjHmCo4yym3kpRbRQxvNZ3KsX1SZcJoVAOG38+TfDaLd8s0sOWWDUHm6KZAbC+YFFtxtSiP80jUWP56xUnRHn9UCbMLQ3nxyhTTgl01gBWPQK/AR8KxYePMbIVAAAAAElFTkSuQmCC',
                    'ts' => now()->timestamp,
                ],
            ],
        ];
    }
}
