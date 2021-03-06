<?php
/**
 * The event to call when snapin completes
 *
 * PHP version 5
 *
 * @category SnapinComplete_Slack
 * @package  FOGProject
 * @author   Tom Elliott <tommygunsster@gmail.com>
 * @license  http://opensource.org/licenses/gpl-3.0 GPLv3
 * @link     https://fogproject.org
 */
/**
 * The event to call when snapin completes
 *
 * @category SnapinComplete_Slack
 * @package  FOGProject
 * @author   Tom Elliott <tommygunsster@gmail.com>
 * @license  http://opensource.org/licenses/gpl-3.0 GPLv3
 * @link     https://fogproject.org
 */
class SnapinComplete_Slack extends PushbulletExtends
{
    /**
     * The name of this event
     *
     * @var string
     */
    protected $name = 'SnapinComplete_Slack';
    /**
     * The description of this event
     *
     * @var string
     */
    protected $description = 'Triggers when a host completes snapin taskings';
    /**
     * The event is active
     *
     * @var bool
     */
    public $active = true;
    /**
     * Perform action
     *
     * @param string $event the event to enact
     * @param mixed  $data  the data
     *
     * @return void
     */
    public function onEvent($event, $data)
    {
        $Objects = self::getClass('SlackManager')
            ->find();
        foreach ((array)$Objects as &$Token) {
            if (!$Token->isValid()) {
                continue;
            }
            self::$message = sprintf(
                'Host %s has completed snapin tasking.',
                $data['Host']->get('name')
            );
            self::$shortdesc = 'Snapin(s) Complete';
            $Token->call('chat.postMessage', $message);
            unset($Token);
        }
        parent::onEvent($event, $data);
    }
}
$EventManager->register(
    'HOST_SNAPIN_COMPLETE',
    new SnapinComplete_Slack()
);
