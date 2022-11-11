<?php

namespace Foundation\Http\Client;

use Foundation\Http\Client\Contract\AndersPink;
use Foundation\Http\Client\Response;

final class AndersPinkClient extends HttpClient implements AndersPink
{
    private $time = '3-days';

    private $limit = 2000;


    public function __construct()
    {
        parent::__construct(config('client.anders_pink'));
    }

    /**
     * @throws \Exception
     */
    public function getBriefings()
    {
        $url = AndersPink::GET_BRIEFINGS;

        $response = $this
            ->get($url);

        return $response;
    }

    /**
     * @throws \JsonException
     */
    public function getBriefing(int $id)
    {
        return $this->get(sprintf('%s/%s?limit=%s&time%s',
            AndersPink::GET_BRIEFINGS,
            $id,
            $this->limit,
            $this->time,
        ));
    }

    /**
     * @throws \JsonException
     */
    public function storeBriefing()
    {
        return $this->post(AndersPink::GET_BRIEFINGS);
    }

    /**
     * @throws \JsonException
     */
    public function updateBriefing(int $id)
    {
        return $this->put(sprintf('%s/%s', AndersPink::GET_BRIEFINGS, $id));
    }

    /**
     * @throws \JsonException
     */
    public function deleteBriefing(int $id)
    {
        return $this->delete(sprintf('%s/%s', AndersPink::GET_BRIEFINGS, $id));
    }

    /**
     * @throws \JsonException
     */
    public function previewBriefing()
    {
        return $this->put(AndersPink::BRIEFING_PREVIEW);
    }

    public function trainBriefing(int $id)
    {
        // TODO: Implement trainBriefing() method.
    }

    /**
     * @throws \JsonException
     */
    public function getBoards()
    {
        return $this->get(AndersPink::BRIEFING_BOARD);
    }

    /**
     * @throws \JsonException
     */
    public function getBoard(int $id)
    {
        return $this->get(AndersPink::BRIEFING_BOARD);
    }

    /**
     * @throws \JsonException
     */
    public function getSuggestedSources(string $keyword)
    {
        return $this->post(sprintf('%s/%s', AndersPink::BRIEFING_SOURCE_KEYWORDS, $keyword));
    }

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
