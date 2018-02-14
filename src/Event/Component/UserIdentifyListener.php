<?php
namespace App\Event\Component;

use Cake\Auth\BaseAuthenticate;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\ORM\TableRegistry;

class UserIdentifyListener implements EventListenerInterface
{
    /**
     * {@inheritDoc}
     */
    public function implementedEvents()
    {
        return [
            'Auth.afterIdentify' => 'afterIdentify'
        ];
    }

    /**
     * Add 'name' parameter to Auth user information.
     *
     * @param \Cake\Event\Event $event Event instance
     * @param array $user User info
     * @param \Cake\Auth\BaseAuthenticate $auth Authentication adapter instance
     * @return void
     */
    public function afterIdentify(Event $event, array $user, BaseAuthenticate $auth)
    {
        $userModel = $auth->config('userModel');
        // skip if user model is not defined
        if (empty($userModel)) {
            return;
        }

        $table = TableRegistry::get($userModel);
        // get user entity
        $entity = $table->get($user['id']);

        // add user 'name' to user info array
        $user['name'] = $entity->name;
        $event->result = $user;
    }
}
