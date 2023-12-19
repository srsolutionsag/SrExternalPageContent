<?php

/*********************************************************************
 * This Code is licensed under the GPL-3.0 License and is Part of a
 * ILIAS Plugin developed by sr solutions ag in Switzerland.
 *
 * https://sr.solutions
 *
 *********************************************************************/

declare(strict_types=1);

namespace srag\Plugins\SrExternalPageContent\Helper;

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Filesystem\Stream\Streams;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
trait HTTPState
{
    protected function outAndEnd(GlobalHttpState $http, string $data): void
    {
        $response = $http->response()->withBody(
            Streams::ofString($data)
        );
        $http->saveResponse($response);
        $http->sendResponse();
        $http->close();
    }

    protected function resolveItemsFromRequest(string $key): array
    {
        $items = [];
        $request = $this->http->request();
        $get = $request->getQueryParams();
        if (isset($get[$key])) {
            foreach (is_array($get[$key] ?? []) ? $get[$key] : [$get[$key]] as $item) {
                $items[] = $item;
            }
        }

        $post = $request->getParsedBody();
        if (isset($post[$key])) {
            foreach (is_array($post[$key] ?? []) ? $post[$key] : [$post[$key]] as $item) {
                $items[] = $item;
            }
        }
        foreach ($post['interruptive_items'] ?? [] as $item) {
            $items[] = $item;
        }

        return $items;
    }
}
