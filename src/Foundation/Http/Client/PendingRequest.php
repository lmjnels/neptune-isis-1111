<?php

namespace Foundation\Http\Client;

use Foundation\Http\Client\Response;

class PendingRequest extends HttpClient
{

    /**
     * @inheritDoc
     */
    protected function dispatchResponseReceivedEvent(Response $response)
    {
        // TODO: Implement dispatchResponseReceivedEvent() method.
    }
    /**
     * @inheritDoc
     */
    protected function dispatchConnectionFailedEvent()
    {
        // TODO: Implement dispatchConnectionFailedEvent() method.
    }
}
