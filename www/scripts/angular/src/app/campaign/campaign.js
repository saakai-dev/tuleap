import angular from 'angular';
import ui_router from 'angular-ui-router';

import 'restangular';
import 'angular-gettext';
import 'angular-ui-utils';

import CampaignConfig from './campaign-config.js';
import CampaignService from './campaign-service.js';
import CampaignCtrl from './campaign-controller.js';
import CampaignListCtrl from './campaign-list-controller.js';
import CampaignNewCtrl from './campaign-new-controller.js';
import CampaignEditCtrl from './campaign-edit-controller.js';
import CurrentPageFilter from './campaign-new-filter.js';

export default angular.module('campaign', [
    ui_router,
    'restangular',
    'gettext',
    'ui.unique'
])
.config(CampaignConfig)
.service('CampaignService', CampaignService)
.controller('CampaignCtrl', CampaignCtrl)
.controller('CampaignListCtrl', CampaignListCtrl)
.controller('CampaignNewCtrl', CampaignNewCtrl)
.controller('CampaignEditCtrl', CampaignEditCtrl)
.filter('CurrentPageFilter', CurrentPageFilter)
.constant('CampaignEditConstants', {
    'SELECTION_STATES': {
        unselected: 'unselected',
        selected  : 'selected',
        added     : 'added',
        removed   : 'removed',
        all       : 'all',
        some      : 'some',
        none      : 'none',
    },
})
.name;

