<?php


namespace Foundation\Http\Client\Contract;


interface AndersPink
{
    public const GET_BRIEFINGS = 'briefings';

    public const BRIEFING_PREVIEW = 'briefing-previews';

    public const BRIEFING_TRAINING = 'briefings/:id/trainings';

    public const BRIEFING_BOARD = 'boards';

    public const BRIEFING_SOURCE_KEYWORDS = 'sources/:keyword';


    /**
     * Get a list of all briefings tied to your api account
     *
     * @return mixed
     */
    public function getBriefings();

    /**
     * Get back a particular briefing and its articles. If you or your team own the briefing
     * you'll see the settings for it (such as keywords)
     *
     * @param integer $id
     *
     * @return mixed
     */
    public function getBriefing(int $id);

    /**
     * Create a new briefing, using all the same options available in the web app
     * (for full API users only)
     *
     * @return mixed
     */
    public function storeBriefing();

    /**
     * Update a particular briefing that is tied to your api account (for full API users only)
     *
     * @param integer $id
     *
     * @return mixed
     */
    public function updateBriefing(int $id);

    /**
     * Delete a particular briefing that is tied to your api account (for full API users only)
     *
     * @param integer $id
     *
     * @return mixed
     */
    public function deleteBriefing(int $id);

    /**
     * Previews a new briefing by supplying the same config as used when making a briefing
     * (for full API users only)
     *
     * @return mixed
     */
    public function previewBriefing();

    /**
     * Trains a briefing by supplying an article id, and whether it's relevant or not
     * (for full API users only)
     *
     * @param integer $id
     *
     * @return mixed
     */
    public function trainBriefing(int $id);

    /**
     * Get a list of all the saved boards tied to your api account
     *
     * @return mixed
     */
    public function getBoards();

    /**
     * Get back a particular saved board and its articles
     *
     * @param integer $id
     *
     * @return mixed
     */
    public function getBoard(int $id);

    /**
     * @param string $keyword
     *
     * @return mixed
     */
    public function getSuggestedSources(string $keyword);


}
