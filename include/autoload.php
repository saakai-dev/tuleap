<?php
// @codingStandardsIgnoreFile
// @codeCoverageIgnoreStart
// this is an autogenerated file - do not edit
function autoloaddcfeaad8e67215ab8493958f6b9c59a8($class) {
    static $classes = null;
    if ($classes === null) {
        $classes = array(
            'testmanagementplugin' => '/testmanagementPlugin.class.php',
            'tuleap\\testmanagement\\admincontroller' => '/TestManagement/AdminController.class.php',
            'tuleap\\testmanagement\\administration\\stepfieldusagedetector' => '/TestManagement/Administration/StepFieldUsageDetector.php',
            'tuleap\\testmanagement\\adminpresenter' => '/TestManagement/AdminPresenter.class.php',
            'tuleap\\testmanagement\\agiledashboardpaneinfo' => '/TestManagement/AgileDashboardPaneInfo.php',
            'tuleap\\testmanagement\\artifactdao' => '/TestManagement/ArtifactDao.php',
            'tuleap\\testmanagement\\artifactfactory' => '/TestManagement/ArtifactFactory.php',
            'tuleap\\testmanagement\\breadcrumbs\\admininistrationbreadcrumbs' => '/TestManagement/Breadcrumbs/AdmininistrationBreadcrumbs.php',
            'tuleap\\testmanagement\\breadcrumbs\\breadcrumbs' => '/TestManagement/Breadcrumbs/Breadcrumbs.php',
            'tuleap\\testmanagement\\breadcrumbs\\nocrumb' => '/TestManagement/Breadcrumbs/NoCrumb.php',
            'tuleap\\testmanagement\\campaign\\artifactnotfoundexception' => '/TestManagement/Campaign/ArtifactNotFoundException.php',
            'tuleap\\testmanagement\\campaign\\automatedtests\\automatedteststriggerer' => '/TestManagement/Campaign/AutomatedTests/AutomatedTestsTriggerer.php',
            'tuleap\\testmanagement\\campaign\\automatedtests\\nojobconfiguredforcampaignexception' => '/TestManagement/Campaign/AutomatedTests/NoJobConfiguredForCampaignException.php',
            'tuleap\\testmanagement\\campaign\\campaign' => '/TestManagement/Campaign/Campaign.php',
            'tuleap\\testmanagement\\campaign\\campaigndao' => '/TestManagement/Campaign/CampaignDao.php',
            'tuleap\\testmanagement\\campaign\\campaignretriever' => '/TestManagement/Campaign/CampaignRetriever.php',
            'tuleap\\testmanagement\\campaign\\campaignsaver' => '/TestManagement/Campaign/CampaignSaver.php',
            'tuleap\\testmanagement\\campaign\\execution\\definitionforexecutionretriever' => '/TestManagement/Campaign/Execution/DefinitionForExecutionRetriever.php',
            'tuleap\\testmanagement\\campaign\\execution\\executiondao' => '/TestManagement/Campaign/Execution/ExecutionDao.php',
            'tuleap\\testmanagement\\campaign\\execution\\paginatedexecutions' => '/TestManagement/Campaign/Execution/PaginatedExecutions.php',
            'tuleap\\testmanagement\\campaign\\jobconfiguration' => '/TestManagement/Campaign/JobConfiguration.php',
            'tuleap\\testmanagement\\campaign\\nojobconfiguration' => '/TestManagement/Campaign/NoJobConfiguration.php',
            'tuleap\\testmanagement\\config' => '/TestManagement/Config.class.php',
            'tuleap\\testmanagement\\configconformancevalidator' => '/TestManagement/ConfigConformanceValidator.class.php',
            'tuleap\\testmanagement\\criterion\\isearchonmilestone' => '/TestManagement/Criterion/ISearchOnMilestone.php',
            'tuleap\\testmanagement\\criterion\\isearchonstatus' => '/TestManagement/Criterion/ISearchOnStatus.php',
            'tuleap\\testmanagement\\criterion\\milestoneall' => '/TestManagement/Criterion/MilestoneAll.php',
            'tuleap\\testmanagement\\criterion\\milestonefilter' => '/TestManagement/Criterion/MilestoneFilter.php',
            'tuleap\\testmanagement\\criterion\\statusall' => '/TestManagement/Criterion/StatusAll.php',
            'tuleap\\testmanagement\\criterion\\statusclosed' => '/TestManagement/Criterion/StatusClosed.php',
            'tuleap\\testmanagement\\criterion\\statusopen' => '/TestManagement/Criterion/StatusOpen.php',
            'tuleap\\testmanagement\\dao' => '/TestManagement/Dao.class.php',
            'tuleap\\testmanagement\\event\\getitemsfrommilestone' => '/TestManagement/Event/GetItemsFromMilestone.php',
            'tuleap\\testmanagement\\event\\getmilestone' => '/TestManagement/Event/GetMilestone.php',
            'tuleap\\testmanagement\\firstconfigcreator' => '/TestManagement/FirstConfigCreator.class.php',
            'tuleap\\testmanagement\\indexcontroller' => '/TestManagement/IndexController.class.php',
            'tuleap\\testmanagement\\indexpresenter' => '/TestManagement/IndexPresenter.class.php',
            'tuleap\\testmanagement\\labelfieldnotfoundexception' => '/TestManagement/LabelFieldNotFoundException.class.php',
            'tuleap\\testmanagement\\malformedqueryparameterexception' => '/TestManagement/MalformedQueryParameterException.php',
            'tuleap\\testmanagement\\milestoneitemsartifactfactory' => '/TestManagement/REST/MilestoneItemsArtifactFactory.php',
            'tuleap\\testmanagement\\nature\\naturecoveredbyoverrider' => '/TestManagement/Nature/NatureCoveredByOverrider.php',
            'tuleap\\testmanagement\\nature\\naturecoveredbypresenter' => '/TestManagement/Nature/NatureCoveredByPresenter.php',
            'tuleap\\testmanagement\\paginatedcampaignsrepresentations' => '/TestManagement/REST/v1/PaginatedCampaignsRepresentations.php',
            'tuleap\\testmanagement\\querytocriterionconverter' => '/TestManagement/QueryToCriterionConverter.php',
            'tuleap\\testmanagement\\realtime\\realtimemessagesender' => '/RealTime/RealTimeMessageSender.php',
            'tuleap\\testmanagement\\rest\\resourcesinjector' => '/TestManagement/REST/ResourcesInjector.class.php',
            'tuleap\\testmanagement\\rest\\v1\\artifactnodebuilder' => '/TestManagement/REST/v1/ArtifactNodeBuilder.php',
            'tuleap\\testmanagement\\rest\\v1\\artifactnodedao' => '/TestManagement/REST/v1/ArtifactNodeDao.php',
            'tuleap\\testmanagement\\rest\\v1\\assignedtorepresentationbuilder' => '/TestManagement/REST/v1/AssignedToRepresentationBuilder.php',
            'tuleap\\testmanagement\\rest\\v1\\bugrepresentation' => '/TestManagement/REST/v1/BugRepresentation.php',
            'tuleap\\testmanagement\\rest\\v1\\campaigncreator' => '/TestManagement/REST/v1/CampaignCreator.class.php',
            'tuleap\\testmanagement\\rest\\v1\\campaignrepresentation' => '/TestManagement/REST/v1/CampaignRepresentation.class.php',
            'tuleap\\testmanagement\\rest\\v1\\campaignrepresentationbuilder' => '/TestManagement/REST/v1/CampaignRepresentationBuilder.php',
            'tuleap\\testmanagement\\rest\\v1\\campaignsresource' => '/TestManagement/REST/v1/CampaignsResource.class.php',
            'tuleap\\testmanagement\\rest\\v1\\campaignupdater' => '/TestManagement/REST/v1/CampaignUpdater.class.php',
            'tuleap\\testmanagement\\rest\\v1\\definitionrepresentation' => '/TestManagement/REST/v1/DefinitionRepresentation.php',
            'tuleap\\testmanagement\\rest\\v1\\definitionrepresentationbuilder' => '/TestManagement/REST/v1/DefinitionRepresentationBuilder.php',
            'tuleap\\testmanagement\\rest\\v1\\definitionselector' => '/TestManagement/REST/v1/DefinitionSelector.php',
            'tuleap\\testmanagement\\rest\\v1\\definitionsresource' => '/TestManagement/REST/v1/DefinitionsResource.class.php',
            'tuleap\\testmanagement\\rest\\v1\\executioncreator' => '/TestManagement/REST/v1/ExecutionCreator.class.php',
            'tuleap\\testmanagement\\rest\\v1\\executionrepresentation' => '/TestManagement/REST/v1/ExecutionRepresentation.php',
            'tuleap\\testmanagement\\rest\\v1\\executionrepresentationbuilder' => '/TestManagement/REST/v1/ExecutionRepresentationBuilder.php',
            'tuleap\\testmanagement\\rest\\v1\\executionsresource' => '/TestManagement/REST/v1/ExecutionsResource.class.php',
            'tuleap\\testmanagement\\rest\\v1\\jobconfigurationrepresentation' => '/TestManagement/REST/v1/JobConfigurationRepresentation.php',
            'tuleap\\testmanagement\\rest\\v1\\milestonerepresentation' => '/TestManagement/REST/v1/MilestoneRepresentation.class.php',
            'tuleap\\testmanagement\\rest\\v1\\minimaldefinitionrepresentation' => '/TestManagement/REST/v1/MinimalDefinitionRepresentation.php',
            'tuleap\\testmanagement\\rest\\v1\\nodebuilderfactory' => '/TestManagement/REST/v1/NodeBuilderFactory.php',
            'tuleap\\testmanagement\\rest\\v1\\nodereferencerepresentation' => '/TestManagement/REST/v1/NodeReferenceRepresentation.php',
            'tuleap\\testmanagement\\rest\\v1\\noderepresentation' => '/TestManagement/REST/v1/NodeRepresentation.php',
            'tuleap\\testmanagement\\rest\\v1\\noderesource' => '/TestManagement/REST/v1/NodesResource.class.php',
            'tuleap\\testmanagement\\rest\\v1\\patchexecutionrepresentation' => '/TestManagement/REST/v1/PATCHExecutionRepresentation.php',
            'tuleap\\testmanagement\\rest\\v1\\previousresultrepresentation' => '/TestManagement/REST/v1/PreviousResultRepresentation.php',
            'tuleap\\testmanagement\\rest\\v1\\projectresource' => '/TestManagement/REST/v1/ProjectResource.class.php',
            'tuleap\\testmanagement\\rest\\v1\\requirementretriever' => '/TestManagement/REST/v1/RequirementRetriever.php',
            'tuleap\\testmanagement\\rest\\v1\\slicedexecutionrepresentations' => '/TestManagement/REST/v1/SlicedExecutionRepresentations.php',
            'tuleap\\testmanagement\\rest\\v1\\stepdefinitionrepresentation' => '/TestManagement/REST/v1/StepDefinitionRepresentation.php',
            'tuleap\\testmanagement\\rest\\v1\\stepresultrepresentation' => '/TestManagement/REST/v1/StepResultRepresentation.php',
            'tuleap\\testmanagement\\rest\\v1\\stepsresultschangesbuilder' => '/TestManagement/REST/v1/StepsResultsChangesBuilder.php',
            'tuleap\\testmanagement\\rest\\v1\\stepsresultsupdater' => '/TestManagement/REST/v1/StepsResultsUpdater.php',
            'tuleap\\testmanagement\\rest\\v1\\teststatusaccordingtostepsstatuschangesbuilder' => '/TestManagement/REST/v1/TestStatusAccordingToStepsStatusChangesBuilder.php',
            'tuleap\\testmanagement\\router' => '/TestManagement/Router.class.php',
            'tuleap\\testmanagement\\service' => '/TestManagement/Service.class.php',
            'tuleap\\testmanagement\\starttestmanagementcontroller' => '/TestManagement/StartTestManagementController.php',
            'tuleap\\testmanagement\\starttestmanagementpresenter' => '/TestManagement/StartTestManagementPresenter.php',
            'tuleap\\testmanagement\\step\\definition\\field\\stepdefinition' => '/TestManagement/Step/Definition/Field/StepDefinition.php',
            'tuleap\\testmanagement\\step\\definition\\field\\stepdefinitionchangesetvalue' => '/TestManagement/Step/Definition/Field/StepDefinitionChangesetValue.php',
            'tuleap\\testmanagement\\step\\definition\\field\\stepdefinitionchangesetvaluedao' => '/TestManagement/Step/Definition/Field/StepDefinitionChangesetValueDao.php',
            'tuleap\\testmanagement\\step\\definition\\field\\viewadmin' => '/TestManagement/Step/Definition/Field/ViewAdmin.php',
            'tuleap\\testmanagement\\step\\execution\\field\\stepexecution' => '/TestManagement/Step/Execution/Field/StepExecution.php',
            'tuleap\\testmanagement\\step\\execution\\field\\stepexecutionchangesetvalue' => '/TestManagement/Step/Execution/Field/StepExecutionChangesetValue.php',
            'tuleap\\testmanagement\\step\\execution\\field\\stepexecutionchangesetvaluedao' => '/TestManagement/Step/Execution/Field/StepExecutionChangesetValueDao.php',
            'tuleap\\testmanagement\\step\\execution\\field\\viewadmin' => '/TestManagement/Step/Execution/Field/ViewAdmin.php',
            'tuleap\\testmanagement\\step\\execution\\stepresult' => '/TestManagement/Step/Execution/StepResult.php',
            'tuleap\\testmanagement\\step\\execution\\stepresultpresenter' => '/TestManagement/Step/Execution/StepResultPresenter.php',
            'tuleap\\testmanagement\\step\\step' => '/TestManagement/Step/Step.php',
            'tuleap\\testmanagement\\step\\steppresenter' => '/TestManagement/Step/StepPresenter.php',
            'tuleap\\testmanagement\\testmanagementcontroller' => '/TestManagement/TestManagementController.php',
            'tuleap\\testmanagement\\testmanagementplugindescriptor' => '/TestManagement/TestManagementPluginDescriptor.class.php',
            'tuleap\\testmanagement\\testmanagementplugininfo' => '/TestManagement/TestManagementPluginInfo.class.php',
            'tuleap\\testmanagement\\userisnotadministratorexception' => '/TestManagement/UserIsNotAdministratorException.php',
            'tuleap\\testmanagement\\xml\\exporter' => '/TestManagement/XML/Exporter.php',
            'tuleap\\testmanagement\\xmlimport' => '/TestManagement/XMLImport.php'
        );
    }
    $cn = strtolower($class);
    if (isset($classes[$cn])) {
        require dirname(__FILE__) . $classes[$cn];
    }
}
spl_autoload_register('autoloaddcfeaad8e67215ab8493958f6b9c59a8');
// @codeCoverageIgnoreEnd
