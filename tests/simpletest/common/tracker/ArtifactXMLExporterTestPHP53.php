<?php
/**
 * Copyright (c) Enalean, 2014. All Rights Reserved.
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

require_once 'common/tracker/ArtifactXMLExporter.class.php';

abstract class ArtifactXMLExporter_BaseTest extends TuleapTestCase {
    /** @var ArtifactXMLExporter */
    protected $exporter;
    /** @var ArtifactXMLExporterDao */
    protected $dao;
    /** @var SimpleXMLElement */
    protected $xml;
    /** @var DomDocument */
    protected $dom;
    protected $tracker_id = 1;
    protected $fixtures_dir;
    protected $open_date = 1234567890; // the same as in fixtures
    protected $expected_open_date;
    protected $archive;

    public function setUp() {
        parent::setUp();
        $this->dao                = mock('ArtifactXMLExporterDao');
        $this->dom                = new DOMDocument("1.0", "UTF8");
        $this->archive            = mock('ZipArchive');
        $node_helper              = new ArtifactXMLNodeHelper($this->dom);
        $attachment_exporter      = new ArtifactAttachmentXMLExporter($node_helper, $this->dao, $this->archive, false);
        $this->exporter           = new ArtifactXMLExporter($this->dao, $attachment_exporter, $node_helper, mock('Logger'));
        $this->fixtures_dir       = dirname(__FILE__) .'/_fixtures/';
        $this->expected_open_date = $this->toExpectedDate($this->open_date);
        Config::store();
        Config::set('sys_data_dir', dirname($this->fixtures_dir));
    }

    public function tearDown() {
        Config::restore();
        parent::tearDown();
    }

    protected function toExpectedDate($timestamp) {
        return date('c', $timestamp);
    }

    protected function exportTrackerDataFromFixture($fixture) {
        $this->loadFixtures($fixture);
        $this->exporter->exportTrackerData($this->tracker_id);
        $this->xml = simplexml_import_dom($this->dom);
    }

    private function loadFixtures($fixture) {
        $file_path = $this->fixtures_dir . $fixture .'.json';
        $fixture_content = file_get_contents($file_path);
        if ($fixture_content == false) {
            throw new Exception("Unable to load $file_path (mis-typed?)");
        }
        $json = $this->decodeJson($fixture_content);

        foreach ($json['artifact'] as $tracker_id => $artifact_rows) {
            stub($this->dao)->searchArtifacts($tracker_id)->returnsDarFromArray($artifact_rows);
        }
        foreach ($json['artifact_history'] as $artifact_id => $history_rows) {
            stub($this->dao)->searchHistory($artifact_id)->returnsDarFromArray($history_rows);
        }
        if (isset($json['artifact_file'])) {
            foreach ($json['artifact_file'] as $artifact_id => $file_rows) {
                stub($this->dao)->searchFilesForArtifact($artifact_id)->returnsDarFromArray($file_rows);
            }
        } else {
            stub($this->dao)->searchFilesForArtifact($artifact_id)->returnsEmptyDar();
        }
        if (isset($json['artifact_file_search'])) {
            foreach ($json['artifact_file_search'] as $artifact_file) {
                $params = $artifact_file['parameters'];
                stub($this->dao)->searchFile($params['artifact_id'], $params['filename'], $params['submitted_by'], $params['date'])->returnsDarFromArray($artifact_file['rows']);
            }
        } else {
            stub($this->dao)->searchFile($artifact_id)->returnsEmptyDar();
        }
        if (isset($json['search_file_before'])) {
            foreach ($json['search_file_before'] as $artifact_file) {
                $params = $artifact_file['parameters'];
                stub($this->dao)->searchFileBefore($params['artifact_id'], $params['filename'], $params['date'])->returnsDarFromArray($artifact_file['rows']);
            }
        } else {
            stub($this->dao)->searchFileBefore($artifact_id)->returnsEmptyDar();
        }
        if (isset($json['search_cc_at'])) {
            foreach ($json['search_cc_at'] as $artifact_cc) {
                $params = $artifact_cc['parameters'];
                stub($this->dao)->searchCCAt($params['artifact_id'], $params['submitted_by'], $params['date'])->returnsDarFromArray($artifact_cc['rows']);
            }
        } else {
            stub($this->dao)->searchCCAt($artifact_id)->returnsEmptyDar();
        }

        if (isset($json['permissions'])) {
            foreach ($json['permissions'] as $artifact_id => $perms) {
                stub($this->dao)->searchPermsForArtifact($artifact_id)->returnsDarFromArray($perms);
            }
        } else {
            stub($this->dao)->searchPermsForArtifact()->returnsEmptyDar();
        }

        if (isset($json['artifact_field_value'])) {
            foreach ($json['artifact_field_value'] as $artifact_id => $history_rows) {
                stub($this->dao)->searchFieldValues($artifact_id)->returnsDarFromArray($history_rows);
            }
        } else {
            stub($this->dao)->searchFieldValues()->returnsEmptyDar();
        }

        if (isset($json['artifact_field_value_list'])) {
            foreach ($json['artifact_field_value_list'] as $artifact_field_value_list) {
                $params = $artifact_field_value_list['parameters'];
                stub($this->dao)->searchFieldValuesList($params['group_artifact_id'], $params['field_name'])->returnsDarFromArray($artifact_field_value_list['rows']);
            }
        } else {
            stub($this->dao)->searchFieldValuesList($artifact_id)->returnsEmptyDar();
        }

        if (isset($json['user'])) {
            foreach ($json['user'] as $user_id => $user_rows) {
                stub($this->dao)->searchUser("$user_id")->returnsDarFromArray($user_rows);
            }
        } else {
            stub($this->dao)->searchUser()->returnsEmptyDar();
        }
    }


    private function decodeJson($string) {
        $json = json_decode($string, true);

        $json_error = json_last_error();
        if ($json_error !== JSON_ERROR_NONE) {
            $this->throwJsonError($json_error);
        }

        return $json;
    }

    private function throwJsonError($json_error) {
        $msg = '';
        switch ($json_error) {
            case JSON_ERROR_NONE:
                $msg = 'No errors';
            break;
            case JSON_ERROR_DEPTH:
                $msg = 'Maximum stack depth exceeded';
            break;
            case JSON_ERROR_STATE_MISMATCH:
                $msg = 'Underflow or the modes mismatch';
            break;
            case JSON_ERROR_CTRL_CHAR:
                $msg = 'Unexpected control character found';
            break;
            case JSON_ERROR_SYNTAX:
                $msg = 'Syntax error, malformed JSON';
            break;
            case JSON_ERROR_UTF8:
                $msg = 'Malformed UTF-8 characters, possibly incorrectly encoded';
            break;
            default:
                $msg = 'Unknown error';
            break;
        }

        throw new Exception($msg);
    }

    protected function dumpXML() {
        $xsl = new DOMDocument();
        $xsl->load(dirname(__FILE__).'/../../../../src/utils/xml/indent.xsl');

        $proc = new XSLTProcessor();
        $proc->importStyleSheet($xsl);
        echo '<pre>'.htmlentities($proc->transformToXML($this->dom)).'</pre>';
    }

    protected function findValue(SimpleXMLElement $field_change, $name) {
        foreach($field_change as $change) {
            if ($change['field_name'] == $name) {
                return $change;
            }
        }
        throw new Exception("$name not found");
    }
}

class ArtifactXMLExporter_SummaryTest extends ArtifactXMLExporter_BaseTest {

    public function itCreatesAnInitialChangesetATheTimeOfOpenDateWhenThereIsNoHistory() {
        $this->exportTrackerDataFromFixture('artifact_without_any_history');

        $this->assertEqual((string)$this->xml->artifact->changeset[0]->field_change[0]->value, 'Le artifact without history');
        $this->assertEqual((string)$this->xml->artifact->changeset[0]->field_change[0]['type'], 'string');
        $this->assertEqual((string)$this->xml->artifact->changeset[0]->field_change[0]['field_name'], 'summary');
        $this->assertEqual((string)$this->xml->artifact->changeset[0]->field_change[1]->value, 'Le original submission');
        $this->assertEqual((string)$this->xml->artifact->changeset[0]->field_change[1]['type'], 'text');
        $this->assertEqual((string)$this->xml->artifact->changeset[0]->field_change[1]['field_name'], 'details');
        $this->assertEqual((string)$this->xml->artifact->changeset[0]->submitted_on, $this->expected_open_date);
    }

    public function itCreatesAnInitialChangesetBasedOnTheOldestValueKnownWhenThereIsHistory() {
        $this->exportTrackerDataFromFixture('artifact_with_full_history');

        $this->assertEqual((string)$this->xml->artifact->changeset[0]->field_change[0]->value, 'Le artifact');
        $this->assertEqual((string)$this->xml->artifact->changeset[0]->field_change[1]->value, 'Le original submission that will be updated');
        $this->assertEqual((string)$this->xml->artifact->changeset[2]->field_change[1]->value, 'Le original submission');
        $this->assertEqual((string)$this->xml->artifact->changeset[0]->submitted_on, $this->expected_open_date);
    }

    public function itCreatesAChangesetForEachHistoryEntry() {
        $this->exportTrackerDataFromFixture('artifact_with_full_history');

        $this->assertEqual((string)$this->xml->artifact->changeset[1]->field_change->value, 'Le artifact with history');
        $this->assertEqual((string)$this->xml->artifact->changeset[1]->submitted_on, $this->toExpectedDate(2234567890));
        $this->assertEqual((string)$this->xml->artifact->changeset[2]->field_change->value, 'Le artifact with full history');
        $this->assertEqual((string)$this->xml->artifact->changeset[2]->submitted_on, $this->toExpectedDate(3234567890));
    }

    public function itCreatesALastChangesetAtImportTimeWhenHistoryDiffersFromCurrentState() {
        $this->exportTrackerDataFromFixture('artifact_with_half_history');

        $this->assertEqual((string)$this->xml->artifact->changeset[3]->field_change->value, 'Le artifact with half history');
        $this->assertEqual((string)$this->xml->artifact->changeset[3]->submitted_on, $this->toExpectedDate($_SERVER['REQUEST_TIME']));
    }

    public function itDoesntMessPreviousArtifactWhenTryingToUpdateInitialChangeset() {
        $this->exportTrackerDataFromFixture('two_artifacts');

        $this->assertCount($this->xml->artifact, 2);

        $this->assertEqual((string)$this->xml->artifact[0]->changeset[0]->field_change->value, 'Le artifact with full history');
        $this->assertEqual((string)$this->xml->artifact[1]->changeset[0]->field_change->value, 'Le artifact');
        $this->assertEqual((string)$this->xml->artifact[1]->changeset[1]->field_change->value, 'The second one');
    }
}

class ArtifactXMLExporter_AttachmentTest extends ArtifactXMLExporter_BaseTest {

    public function itCreatesAChangesetWithOneAttachment() {
        $this->exportTrackerDataFromFixture('artifact_with_one_attachment');
        $this->assertCount($this->xml->artifact->changeset, 2);

        expect($this->archive)->addEmptyDir('data')->once();

        $this->assertEqual((string)$this->xml->artifact->changeset[1]->field_change['field_name'], 'attachment');
        $this->assertEqual((string)$this->xml->artifact->changeset[1]->field_change['type'], 'file');
        $this->assertEqual((string)$this->xml->artifact->changeset[1]->field_change->value[0]['ref'], 'File30');
        $this->assertEqual((string)$this->xml->artifact->changeset[1]->submitted_on, $this->toExpectedDate(3234567900));

        expect($this->archive)->addFile($this->fixtures_dir.'/'.ArtifactFile::ROOT_DIRNAME.'/1/30','data/ArtifactFile30.bin')->once();

        $this->assertCount($this->xml->artifact->file, 1);
        $this->assertEqual((string)$this->xml->artifact->file[0]['id'], 'File30');
        $this->assertEqual((string)$this->xml->artifact->file[0]->filename, 'A.png');
        $this->assertEqual((int)   $this->xml->artifact->file[0]->filesize, 12323);
        $this->assertEqual((string)$this->xml->artifact->file[0]->filetype, 'image/png');
        $this->assertEqual((string)$this->xml->artifact->file[0]->description, 'The screenshot');
    }

    public function itCreatesAChangesetWithTwoAttachmentsWithSameName() {
        $this->exportTrackerDataFromFixture('artifact_with_two_attachments_same_name');
        
        $this->assertCount($this->xml->artifact->changeset, 3);

        $this->assertEqual((string)$this->xml->artifact->changeset[1]->field_change['field_name'], 'attachment');
        $this->assertEqual((string)$this->xml->artifact->changeset[1]->field_change->value[0]['ref'], 'File30');

        $this->assertEqual((string)$this->xml->artifact->changeset[2]->field_change['field_name'], 'attachment');
        $this->assertCount($this->xml->artifact->changeset[2]->field_change->value, 2);
        $this->assertEqual((string)$this->xml->artifact->changeset[2]->field_change->value[0]['ref'], 'File31');
        $this->assertEqual((string)$this->xml->artifact->changeset[2]->field_change->value[1]['ref'], 'File30');
        $this->assertEqual((string)$this->xml->artifact->changeset[2]->submitted_on, $this->toExpectedDate(3234568000));

        $this->assertCount($this->xml->artifact->file, 2);
        $this->assertEqual((string)$this->xml->artifact->file[0]['id'], 'File30');
        $this->assertEqual((string)$this->xml->artifact->file[0]->filename, 'A.png');
        $this->assertEqual((int)   $this->xml->artifact->file[0]->filesize, 12323);
        $this->assertEqual((string)$this->xml->artifact->file[0]->filetype, 'image/png');
        $this->assertEqual((string)$this->xml->artifact->file[0]->description, 'The screenshot');

        $this->assertEqual((string)$this->xml->artifact->file[1]['id'], 'File31');
        $this->assertEqual((string)$this->xml->artifact->file[1]->filename, 'A.png');
        $this->assertEqual((int)   $this->xml->artifact->file[1]->filesize, 50);
        $this->assertEqual((string)$this->xml->artifact->file[1]->filetype, 'image/png');
        $this->assertEqual((string)$this->xml->artifact->file[1]->description, 'The screenshot v2');
    }

    public function itCreatesAChangesetWithDeletedAttachments() {
        $this->exportTrackerDataFromFixture('artifact_with_deleted_attachment');

        $this->assertCount($this->xml->artifact->changeset, 2);

        $this->assertEqual((string)$this->xml->artifact->changeset[1]->field_change['field_name'], 'attachment');
        $this->assertCount($this->xml->artifact->changeset[1]->field_change->value, 1);
        $this->assertEqual((string)$this->xml->artifact->changeset[1]->field_change->value[0]['ref'], 'File31');
        $this->assertEqual((string)$this->xml->artifact->changeset[1]->submitted_on, $this->toExpectedDate(3234568000));

        $this->assertCount($this->xml->artifact->file, 1);
        $this->assertEqual((string)$this->xml->artifact->file[0]['id'], 'File31');
        $this->assertEqual((string)$this->xml->artifact->file[0]->filename, 'zzz.pdf');
    }
}

class ArtifactXMLExporter_CCTest extends ArtifactXMLExporter_BaseTest {

    public function itCreatesAnInitialChangesetATheTimeOfOpenDateWhenThereIsNoHistory() {
        $this->exportTrackerDataFromFixture('artifact_cc_no_changes');

        $this->assertCount($this->xml->artifact->changeset[0]->field_change, 2);
        $this->assertChangesItCreatesAnInitialChangesetATheTimeOfOpenDateWhenThereIsNoHistory($this->xml->artifact->changeset[0]->field_change[0]);
        $this->assertChangesItCreatesAnInitialChangesetATheTimeOfOpenDateWhenThereIsNoHistory($this->xml->artifact->changeset[0]->field_change[1]);
    }

    private function assertChangesItCreatesAnInitialChangesetATheTimeOfOpenDateWhenThereIsNoHistory(SimpleXMLElement $field_change) {
        switch($field_change['field_name']) {
            case 'cc':
                $this->assertEqual((string)$field_change['type'], 'open_list');
                $this->assertEqual((string)$field_change['bind'], 'user');
                $this->assertEqual((string)$field_change->value[0], 'john@doe.org');
                $this->assertEqual((string)$field_change->value[0]['format'], 'email');
                $this->assertEqual((string)$field_change->value[1], 'jeanjean');
                $this->assertEqual((string)$field_change->value[1]['format'], 'username');
                break;
            case 'summary':
                // Ok but we don't care
                break;
            default:
                throw new Exception('Unexpected field type: '.$field_change['field_name']);
                break;
        }
    }

    public function itCreatesTheTwoCCChangesChangeset() {
        $this->exportTrackerDataFromFixture('artifact_cc_add_new');

        $this->assertCount($this->xml->artifact->changeset, 3);

        $this->assertEqual((string)$this->xml->artifact->changeset[1]->field_change->value, 'john@doe.org');
        $this->assertEqual((string)$this->xml->artifact->changeset[2]->field_change->value[0], 'john@doe.org');
        $this->assertEqual((string)$this->xml->artifact->changeset[2]->field_change->value[1], 'jeanjean');
    }

    public function itCreatesChangesWithDeletedCC() {
        $this->exportTrackerDataFromFixture('artifact_cc_remove');

        $this->assertCount($this->xml->artifact->changeset, 2);

        $this->assertCount($this->xml->artifact->changeset[0]->field_change->value, 3);
        $this->assertEqual((string)$this->xml->artifact->changeset[0]->field_change->value[0], 'john@doe.org');
        $this->assertEqual((string)$this->xml->artifact->changeset[0]->field_change->value[1], 'jeanjean');
        $this->assertEqual((string)$this->xml->artifact->changeset[0]->field_change->value[2], 'bla@bla.org');

        $this->assertCount($this->xml->artifact->changeset[1]->field_change->value, 1);
        $this->assertEqual((string)$this->xml->artifact->changeset[1]->field_change->value, 'john@doe.org');
    }
}

class ArtifactXMLExporter_AttachmentAndSummaryTest extends ArtifactXMLExporter_BaseTest {

    public function itCreatesASingleChangesetWithSummaryAndAttachment() {
        $this->exportTrackerDataFromFixture('artifact_with_summary_and_attachment');
        $this->assertCount($this->xml->artifact->changeset, 1);

        $this->assertEqual((string)$this->xml->artifact->changeset[0]->submitted_on, $this->toExpectedDate(1234567890));

        // cannot guarranty the order of execution therefore specific assertion in dedicated method
        $this->assertCount($this->xml->artifact->changeset[0]->field_change, 2);
        $this->assertChangesItCreatesASingleChangesetWithSummaryAndAttachment($this->xml->artifact->changeset[0]->field_change[0]);
        $this->assertChangesItCreatesASingleChangesetWithSummaryAndAttachment($this->xml->artifact->changeset[0]->field_change[1]);

        $this->assertCount($this->xml->artifact->file, 1);
    }

    private function assertChangesItCreatesASingleChangesetWithSummaryAndAttachment(SimpleXMLElement $field_change) {
        switch($field_change['field_name']) {
            case 'attachment':
                $this->assertEqual($field_change->value[0]['ref'], 'File30');
                break;
            case 'summary':
                $this->assertEqual($field_change->value, 'Le artifact with full history');
                break;
            default:
                throw new Exception('Unexpected field type: '.$field_change['field_name']);
                break;
        }
    }

    public function itCreatesChangesetWithSummaryAndAttachmentChange() {
        $this->exportTrackerDataFromFixture('artifact_with_summary_and_attachment_change');

        $this->assertCount($this->xml->artifact->changeset, 3);

        // Changeset1: original summary
        $this->assertEqual((string)$this->xml->artifact->changeset[0]->submitted_on, $this->toExpectedDate(1234567890));
        $this->assertEqual((string)$this->xml->artifact->changeset[0]->field_change['field_name'], 'summary');
        $this->assertEqual((string)$this->xml->artifact->changeset[0]->field_change->value, 'Le artifact with full history');

        // Changeset2: attachment
        $this->assertEqual((string)$this->xml->artifact->changeset[1]->submitted_on, $this->toExpectedDate(1234568000));
        $this->assertEqual((string)$this->xml->artifact->changeset[1]->field_change['field_name'], 'attachment');
        $this->assertEqual((string)$this->xml->artifact->changeset[1]->field_change->value[0]['ref'], 'File30');

        // Changeset3: new summary
        $this->assertEqual((string)$this->xml->artifact->changeset[2]->submitted_on, $this->toExpectedDate(1234569000));
        $this->assertEqual((string)$this->xml->artifact->changeset[2]->field_change['field_name'], 'summary');
        $this->assertEqual((string)$this->xml->artifact->changeset[2]->field_change->value, 'Le artifact with updated summary');

        $this->assertCount($this->xml->artifact->file, 1);
    }

    public function itCreatesChangesetWithAttachmentAndSummaryWhenHistoryDiffersFromCurrentState() {
        $this->exportTrackerDataFromFixture('artifact_with_summary_attachment_half_history');

        $this->assertCount($this->xml->artifact->changeset, 4);

        // Changeset1: original summary
        $this->assertEqual((string)$this->xml->artifact->changeset[0]->submitted_on, $this->toExpectedDate(1234567890));
        $this->assertEqual((string)$this->xml->artifact->changeset[0]->field_change['field_name'], 'summary');
        $this->assertEqual((string)$this->xml->artifact->changeset[0]->field_change->value, 'Le artifact with full history');

        // Changeset2: new summary
        $this->assertEqual((string)$this->xml->artifact->changeset[1]->submitted_on, $this->toExpectedDate(1234568000));
        $this->assertEqual((string)$this->xml->artifact->changeset[1]->field_change['field_name'], 'summary');
        $this->assertEqual((string)$this->xml->artifact->changeset[1]->field_change->value, 'Le artifact with updated summary');

        // Changeset3: attachment
        $this->assertEqual((string)$this->xml->artifact->changeset[2]->submitted_on, $this->toExpectedDate(1234569000));
        $this->assertEqual((string)$this->xml->artifact->changeset[2]->field_change['field_name'], 'attachment');
        $this->assertEqual((string)$this->xml->artifact->changeset[2]->field_change->value[0]['ref'], 'File30');

        // Changeset4: last summary update
        $this->assertEqual((string)$this->xml->artifact->changeset[3]->submitted_on, $this->toExpectedDate($_SERVER['REQUEST_TIME']));
        $this->assertEqual((string)$this->xml->artifact->changeset[3]->field_change['field_name'], 'summary');
        $this->assertEqual((string)$this->xml->artifact->changeset[3]->field_change->value, 'Le artifact with half history');

        $this->assertCount($this->xml->artifact->file, 1);
    }
}

class ArtifactXMLExporter_PermissionsOnArtifactTest extends ArtifactXMLExporter_BaseTest {

    public function itDoesNotExportPermsIfThereIsNoPerms() {
        $this->exportTrackerDataFromFixture('artifact_with_full_history');
        foreach ($this->xml->artifact->xpath('changeset') as $changeset) {
            $this->assertThereIsNoPermissionsFieldChange($changeset);
        }
    }

    public function itCreatesPermsOnArtifactAtTheVeryEnd() {
        $permissions_are_exported = false;
        $this->exportTrackerDataFromFixture('artifact_with_full_history_with_perms_on_artifact');

        $nb_of_changesets = count($this->xml->artifact->changeset);
        $last_changeset   = $this->xml->artifact->changeset[$nb_of_changesets - 1];

        foreach ($last_changeset->field_change as $field_change) {
            if ((string)$field_change['field_name'] !== 'permissions_on_artifact') {
                continue;
            }
            $this->assertEqual((string)$field_change['type'], 'permissions_on_artifact');
            $this->assertEqual((string)$field_change['use_perm'], '1');
            $this->assertCount($field_change->ugroup, 2);
            $this->assertEqual((string)$field_change->ugroup[0]['ugroup_id'], '15');
            $this->assertEqual((string)$field_change->ugroup[1]['ugroup_id'], '101');
            $permissions_are_exported = true;
        }

        $this->assertTrue($permissions_are_exported);
    }

    public function itDoesNotExportPermissionsInFirstChangesets() {
        $this->exportTrackerDataFromFixture('artifact_with_full_history');

        $first_changesets = array_slice($this->xml->artifact->xpath('changeset'), 0, -1);
        foreach ($first_changesets as $changeset) {
            $this->assertThereIsNoPermissionsFieldChange($changeset);
        }
    }

    private function assertThereIsNoPermissionsFieldChange(SimpleXMLElement $changeset) {
        foreach ($changeset->field_change as $field_change) {
            $this->assertNotEqual((string)$field_change['field_name'], 'permissions_on_artifact');
        }
    }
}

class ArtifactXMLExporter_StringFieldTest extends ArtifactXMLExporter_BaseTest {

    public function itCreatesAChangesetForEachHistoryEntry() {
        $this->exportTrackerDataFromFixture('artifact_with_string_history');

        $this->assertCount($this->xml->artifact->changeset, 3);

        $this->assertEqual((string)$this->xml->artifact->changeset[1]->field_change->value, 'The error code is 23232');
        $this->assertEqual((string)$this->xml->artifact->changeset[1]->field_change['field_name'], 'field_14');
        $this->assertEqual((string)$this->xml->artifact->changeset[1]->field_change['type'], 'string');
        $this->assertEqual((string)$this->xml->artifact->changeset[1]->submitted_on, $this->toExpectedDate(3234567890));

        $this->assertEqual((string)$this->xml->artifact->changeset[2]->field_change->value, 'The error code is not returned');
        $this->assertEqual((string)$this->xml->artifact->changeset[2]->field_change['field_name'], 'field_14');
        $this->assertEqual((string)$this->xml->artifact->changeset[2]->field_change['type'], 'string');
        $this->assertEqual((string)$this->xml->artifact->changeset[2]->submitted_on, $this->toExpectedDate(3234570000));
    }

    public function itCreatesAnInitialChangesetATheTimeOfOpenDateWhenThereIsNoHistory() {
        $this->exportTrackerDataFromFixture('artifact_with_string_no_history');

        $this->assertCount($this->xml->artifact->changeset, 1);

        $this->assertCount($this->xml->artifact->changeset[0]->field_change, 2);
        $this->assertChangesItCreatesASingleChangesetWithSummaryAndString($this->xml->artifact->changeset[0]->field_change[0]);
        $this->assertChangesItCreatesASingleChangesetWithSummaryAndString($this->xml->artifact->changeset[0]->field_change[1]);
    }

    private function assertChangesItCreatesASingleChangesetWithSummaryAndString(SimpleXMLElement $field_change) {
        switch($field_change['field_name']) {
            case 'field_14':
                $this->assertEqual($field_change->value, 'The error code is not returned');
                break;
            case 'summary':
                $this->assertEqual($field_change->value, 'Le artifact with full history');
                break;
            default:
                throw new Exception('Unexpected field type: '.$field_change['field_name']);
                break;
        }
    }

    public function itCreatesALastChangesetAtImportTimeWhenHistoryDiffersFromCurrentState() {
        $this->exportTrackerDataFromFixture('artifact_with_string_half_history');

        $this->assertCount($this->xml->artifact->changeset, 3);

        $this->assertCount($this->xml->artifact->changeset[1]->field_change, 1);
        $this->assertEqual((string)$this->xml->artifact->changeset[1]->field_change->value, 'The error code is 23232');
        $this->assertEqual((string)$this->xml->artifact->changeset[1]->field_change['field_name'], 'field_14');
        $this->assertEqual((string)$this->xml->artifact->changeset[1]->submitted_on, $this->toExpectedDate(3234567890));

        $this->assertCount($this->xml->artifact->changeset[2]->field_change, 1);
        $this->assertEqual((string)$this->xml->artifact->changeset[2]->field_change->value, 'The error code is not returned');
        $this->assertEqual((string)$this->xml->artifact->changeset[2]->field_change['field_name'], 'field_14');
        $this->assertEqual((string)$this->xml->artifact->changeset[2]->submitted_on, $this->toExpectedDate($_SERVER['REQUEST_TIME']));
    }
}

class ArtifactXMLExporter_FloatFieldTest extends ArtifactXMLExporter_BaseTest {

    public function itDoesntCreateAnExtraChangesetWhenThereIsAFloatToStringConversionWithTrailingZero() {
        $this->exportTrackerDataFromFixture('artifact_with_float_history');

        $this->assertCount($this->xml->artifact->changeset, 3);

        $this->assertEqual((string)$this->xml->artifact->changeset[1]->field_change->value, '66.98');

        $this->assertEqual((string)$this->xml->artifact->changeset[2]->field_change->value, '2048');
    }
}

class ArtifactXMLExporter_ScalarFieldTest extends ArtifactXMLExporter_BaseTest {

    public function itCreatesAChangesetForEachHistoryEntry() {
        $this->exportTrackerDataFromFixture('artifact_with_scalar_history');

        $this->assertCount($this->xml->artifact->changeset, 6);

        $this->assertCount($this->xml->artifact->changeset[0]->field_change, 6);
        $this->assertEqual((string)$this->findValue($this->xml->artifact->changeset[0]->field_change, 'field_18')->value, '');

        $this->assertEqual((string)$this->xml->artifact->changeset[1]->field_change->value, 'The error code is 23232');
        $this->assertEqual((string)$this->xml->artifact->changeset[1]->field_change['field_name'], 'field_14');
        $this->assertEqual((string)$this->xml->artifact->changeset[1]->field_change['type'], 'string');
        $this->assertEqual((string)$this->xml->artifact->changeset[1]->submitted_on, $this->toExpectedDate(3234567100));

        $this->assertEqual((string)$this->xml->artifact->changeset[2]->field_change->value, "some text");
        $this->assertEqual((string)$this->xml->artifact->changeset[2]->field_change['field_name'], 'field_15');
        $this->assertEqual((string)$this->xml->artifact->changeset[2]->field_change['type'], 'text');
        $this->assertEqual((string)$this->xml->artifact->changeset[2]->submitted_on, $this->toExpectedDate(3234567200));

        $this->assertEqual((string)$this->xml->artifact->changeset[3]->field_change->value, "9001");
        $this->assertEqual((string)$this->xml->artifact->changeset[3]->field_change['field_name'], 'field_16');
        $this->assertEqual((string)$this->xml->artifact->changeset[3]->field_change['type'], 'int');
        $this->assertEqual((string)$this->xml->artifact->changeset[3]->submitted_on, $this->toExpectedDate(3234567300));

        $this->assertEqual((string)$this->xml->artifact->changeset[4]->field_change->value, "66.98");
        $this->assertEqual((string)$this->xml->artifact->changeset[4]->field_change['field_name'], 'field_17');
        $this->assertEqual((string)$this->xml->artifact->changeset[4]->field_change['type'], 'float');
        $this->assertEqual((string)$this->xml->artifact->changeset[4]->submitted_on, $this->toExpectedDate(3234567400));

        $this->assertEqual((string)$this->xml->artifact->changeset[5]->field_change->value, $this->toExpectedDate(1234543210));
        $this->assertEqual((string)$this->xml->artifact->changeset[5]->field_change->value['format'], 'ISO8601');
        $this->assertEqual((string)$this->xml->artifact->changeset[5]->field_change['field_name'], 'field_18');
        $this->assertEqual((string)$this->xml->artifact->changeset[5]->field_change['type'], 'date');
        $this->assertEqual((string)$this->xml->artifact->changeset[5]->submitted_on, $this->toExpectedDate(3234567500));
    }

    public function itCreatesAnInitialChangesetATheTimeOfOpenDateWhenThereIsNoHistory() {
        $this->exportTrackerDataFromFixture('artifact_with_scalar_no_history');

        $this->assertCount($this->xml->artifact->changeset, 1);

        $change = $this->xml->artifact->changeset[0]->field_change;
        $this->assertCount($change, 6);


        $string = $this->findValue($change, 'field_14');
        $this->assertEqual((string)$string->value, 'The error code is 23232');
        $text   = $this->findValue($change, 'field_15');
        $this->assertEqual((string)$text->value, 'some text');
        $int    = $this->findValue($change, 'field_16');
        $this->assertEqual((string)$int->value, '9001');
        $float  = $this->findValue($change, 'field_17');
        $this->assertEqual((string)$float->value, '66.98');
        $date   = $this->findValue($change, 'field_18');
        $this->assertEqual((string)$date->value, $this->toExpectedDate(1234543210));
    }

    public function itCreatesALastChangesetAtImportTimeWhenHistoryDiffersFromCurrentState() {
        $this->exportTrackerDataFromFixture('artifact_with_scalar_half_history');

        $this->assertCount($this->xml->artifact->changeset, 7);

        $change = $this->xml->artifact->changeset[6]->field_change;
        $this->assertCount($change, 5);

        $string = $this->findValue($change, 'field_14');
        $this->assertEqual((string)$string->value, 'The error code is wrong');
        $text   = $this->findValue($change, 'field_15');
        $this->assertEqual((string)$text->value, 'some rant');
        $int    = $this->findValue($change, 'field_16');
        $this->assertEqual((string)$int->value, '987');
        $float  = $this->findValue($change, 'field_17');
        $this->assertEqual((string)$float->value, '3.14');
        $date   = $this->findValue($change, 'field_18');
        $this->assertEqual((string)$date->value, $this->toExpectedDate(1234555555));
    }
}

class ArtifactXMLExporter_CloseDateFieldTest extends ArtifactXMLExporter_BaseTest {

    public function itCreatesTheChangesetWithValueStoredOnArtifactTable() {
        $this->exportTrackerDataFromFixture('artifact_with_close_date_no_history');

        $this->assertCount($this->xml->artifact->changeset, 2);

        $this->assertEqual((string)$this->xml->artifact->changeset[1]->field_change->value, $this->toExpectedDate(1234800000));
        $this->assertEqual((string)$this->xml->artifact->changeset[1]->field_change['field_name'], 'close_date');
        $this->assertEqual((string)$this->xml->artifact->changeset[1]->field_change['type'], 'date');
        $this->assertEqual((string)$this->xml->artifact->changeset[1]->submitted_on, $this->toExpectedDate(1234800000));
    }

    public function itCreatesTheChangesetWhenArtifactIsKeptReopen() {
        $this->exportTrackerDataFromFixture('artifact_with_close_date_kept_reopen');
        $this->assertCount($this->xml->artifact->changeset, 3);

        // 1. Create artifact
        // 2. Close artifact
        $this->assertEqual((string)$this->xml->artifact->changeset[1]->field_change->value, $this->toExpectedDate(1234800000));
        $this->assertEqual((string)$this->xml->artifact->changeset[1]->submitted_on, $this->toExpectedDate(1234800000));
        // 3. Reopen artifact
        $this->assertEqual((string)$this->xml->artifact->changeset[2]->field_change->value, '');
        $this->assertEqual((string)$this->xml->artifact->changeset[2]->submitted_on, $this->toExpectedDate(1234900000));
    }

    public function itCreatesTheChangesetWhenOneOpenAndCloseArtifact() {
        $this->exportTrackerDataFromFixture('artifact_with_close_date_history');

        $this->assertCount($this->xml->artifact->changeset, 5);

        // 1. Create artifact
        // 2. Close artifact
        $this->assertEqual((string)$this->xml->artifact->changeset[1]->field_change->value, $this->toExpectedDate(1234800000));
        $this->assertEqual((string)$this->xml->artifact->changeset[1]->submitted_on, $this->toExpectedDate(1234800000));
        // 3. Reopen artifact
        $this->assertEqual((string)$this->xml->artifact->changeset[2]->field_change->value, '');
        $this->assertEqual((string)$this->xml->artifact->changeset[2]->submitted_on, $this->toExpectedDate(1234810000));
        // 4. Close again artifact
        $this->assertEqual((string)$this->xml->artifact->changeset[3]->field_change->value, $this->toExpectedDate(1234820000));
        $this->assertEqual((string)$this->xml->artifact->changeset[3]->submitted_on, $this->toExpectedDate(1234820000));
        // 5. Change close date
        $this->assertEqual((string)$this->xml->artifact->changeset[4]->field_change->value, $this->toExpectedDate(1234830000));
        $this->assertEqual((string)$this->xml->artifact->changeset[4]->submitted_on, $this->toExpectedDate(1234840000));
    }
}

class ArtifactXMLExporter_StaticListFieldTest extends ArtifactXMLExporter_BaseTest {

    public function itCreatesAChangesetForEachHistoryEntry() {
        $this->exportTrackerDataFromFixture('artifact_with_static_list_history');

        $this->assertCount($this->xml->artifact->changeset, 3);

        $initial_change = $this->findValue($this->xml->artifact->changeset[0]->field_change, 'category_id');
        $this->assertEqual((string)$initial_change->value, 'UI');
        $this->assertEqual((string)$initial_change['field_name'], 'category_id');
        $this->assertEqual((string)$initial_change['type'], 'list');
        $this->assertEqual((string)$initial_change['bind'], 'static');

        $this->assertEqual((string)$this->xml->artifact->changeset[1]->field_change->value, 'Database');
        $this->assertEqual((string)$this->xml->artifact->changeset[1]->field_change['field_name'], 'category_id');
        $this->assertEqual((string)$this->xml->artifact->changeset[1]->field_change['type'], 'list');
        $this->assertEqual((string)$this->xml->artifact->changeset[1]->field_change['bind'], 'static');
        $this->assertEqual((string)$this->xml->artifact->changeset[1]->submitted_on, $this->toExpectedDate(3234567890));

        $this->assertEqual((string)$this->xml->artifact->changeset[2]->field_change->value, '');
        $this->assertEqual((string)$this->xml->artifact->changeset[2]->field_change['field_name'], 'category_id');
        $this->assertEqual((string)$this->xml->artifact->changeset[2]->field_change['type'], 'list');
        $this->assertEqual((string)$this->xml->artifact->changeset[2]->field_change['bind'], 'static');
        $this->assertEqual((string)$this->xml->artifact->changeset[2]->submitted_on, $this->toExpectedDate(3234570000));
    }

    public function itCreatesALastChangesetWhenHistoryWasNotRecorded() {
        $this->exportTrackerDataFromFixture('artifact_with_static_list_half_history');

        $this->assertCount($this->xml->artifact->changeset, 3);

        $this->assertEqual((string)$this->xml->artifact->changeset[2]->field_change->value, 'UI');
        $this->assertEqual((string)$this->xml->artifact->changeset[2]->field_change['field_name'], 'category_id');
        $this->assertEqual((string)$this->xml->artifact->changeset[2]->field_change['type'], 'list');
        $this->assertEqual((string)$this->xml->artifact->changeset[2]->field_change['bind'], 'static');
        $this->assertEqual((string)$this->xml->artifact->changeset[2]->submitted_on, $this->toExpectedDate($_SERVER['REQUEST_TIME']));
    }
}

class ArtifactXMLExporter_UserListFieldTest extends ArtifactXMLExporter_BaseTest {

    public function itCreatesAChangesetForEachHistoryEntry() {
        $this->exportTrackerDataFromFixture('artifact_with_user_list_history');

        $this->assertCount($this->xml->artifact->changeset, 2);

        $initial_change = $this->findValue($this->xml->artifact->changeset[0]->field_change, 'assigned_to');
        $this->assertEqual((string)$initial_change->value, '');
        $this->assertEqual((string)$initial_change['field_name'], 'assigned_to');
        $this->assertEqual((string)$initial_change['type'], 'list');
        $this->assertEqual((string)$initial_change['bind'], 'user');

        $this->assertEqual((string)$this->xml->artifact->changeset[1]->field_change->value, 'jeanjean');
        $this->assertEqual((string)$this->xml->artifact->changeset[1]->field_change->value['format'], 'username');
        $this->assertEqual((string)$this->xml->artifact->changeset[1]->field_change['field_name'], 'assigned_to');
        $this->assertEqual((string)$this->xml->artifact->changeset[1]->field_change['type'], 'list');
        $this->assertEqual((string)$this->xml->artifact->changeset[1]->field_change['bind'], 'user');
        $this->assertEqual((string)$this->xml->artifact->changeset[1]->submitted_on, $this->toExpectedDate(3234567890));
    }
}