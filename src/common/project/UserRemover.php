<?php
/**
 * Copyright (c) Enalean, 2017. All Rights Reserved.
 *
 * This file is a part of Tuleap.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Tuleap\Project;

use ProjectManager;
use EventManager;
use ArtifactTypeFactory;
use Feedback;

class UserRemover
{
    /**
     * @var ProjectManager
     */
    private $project_manager;

    /**
     * @var EventManager
     */
    private $event_manager;

    /**
     * @var ArtifactTypeFactory
     */
    private $tv3_tracker_factory;

    /**
     * @var UserRemoverDao
     */
    private $dao;

    public function __construct(
        ProjectManager $project_manager,
        EventManager $event_manager,
        ArtifactTypeFactory $tv3_tracker_factory,
        UserRemoverDao $dao
    ) {
        $this->project_manager     = $project_manager;
        $this->event_manager       = $event_manager;
        $this->tv3_tracker_factory = $tv3_tracker_factory;
        $this->dao                 = $dao;
    }

    public function removeUserFromProject($project_id, $user_id, $admin_action = true)
    {
        if (! $this->dao->removeUserFromProject($project_id, $user_id)) {
            $GLOBALS['Response']->addFeedback(
                Feedback::ERROR,
                $GLOBALS['Language']->getText('project_admin_index', 'user_not_removed')
            );

            return false;
        }

        $this->event_manager->processEvent('project_admin_remove_user', array(
            'group_id' => $project_id,
            'user_id'  => $user_id
        ));

        $project = $this->project_manager->getProject($project_id);
        if (! $project || ! is_object($project) || $project->isError()) {
            exit_no_group();
        }

        $tv3_trackers = $this->tv3_tracker_factory->getArtifactTypesFromId($project_id);

        if ($tv3_trackers && count($tv3_trackers) > 0) {
            for ($j = 0; $j < count($tv3_trackers); $j++) {
                if (! $tv3_trackers[$j]->deleteUser($user_id)) {
                    $GLOBALS['Response']->addFeedback(
                        'error',
                        $GLOBALS['Language']->getText('project_admin_index', 'del_tracker_perm_fail', $tv3_trackers[$j]->getName())
                    );
                }
            }
        }

        if (! ugroup_delete_user_from_project_ugroups($project_id, $user_id)) {
            $GLOBALS['Response']->addFeedback('error', $GLOBALS['Language']->getText('project_admin_index', 'del_user_from_ug_fail'));
        }

        $name = user_getname($user_id);

        if ($admin_action) {
            $GLOBALS['Response']->addFeedback('info', $GLOBALS['Language']->getText('project_admin_index', 'user_removed').' ('.$name.')');
        } else {
            $GLOBALS['Response']->addFeedback('info', $GLOBALS['Language']->getText('project_admin_index', 'self_user_remove').' ('.$project->getPublicName().')');
        }

        group_add_history('removed_user', user_getname($user_id)." ($user_id)", $project_id);

        return true;
    }
}
