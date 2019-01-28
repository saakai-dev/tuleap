/*
 * Copyright (c) Enalean, 2018-2019. All Rights Reserved.
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

import Vue from "vue";
import { TYPE_FOLDER } from "../constants";

export {
    beginLoading,
    initApp,
    resetErrors,
    saveFolderContent,
    addJustCreatedItemToFolderContent,
    appendSubFolderContent,
    foldFolderContent,
    unfoldFolderContent,
    setFolderLoadingError,
    stopLoading,
    switchFolderPermissionError,
    saveAscendantHierarchy,
    resetAscendantHierarchy,
    beginLoadingAscendantHierarchy,
    stopLoadingAscendantHierarchy,
    appendFolderToAscendantHierarchy,
    setCurrentFolder,
    setModalError,
    resetFoldedLists,
    resetModalError,
    removeCreatedPropertyOnItem,
    replaceUploadingFileWithActualFile,
    removeItemFromFolderContent,
    removeIsUnderConstruction,
    addFileInUploadsList,
    removeFileFromUploadsList,
    emptyFilesUploadsList
};

function saveFolderContent(state, folder_content) {
    state.folder_content = folder_content;
}

function addJustCreatedItemToFolderContent(state, item) {
    const parent = state.folder_content.find(parent => parent.id === item.parent_id);

    item.level = parent ? parent.level + 1 : 0;
    const length = state.folder_content.length;
    let i = 0;
    while (i < length) {
        const possible_sibling = state.folder_content[i];
        const next_sibling = state.folder_content[i + 1];
        const is_sibling = possible_sibling.parent_id === item.parent_id;

        if (is_sibling) {
            const are_both_folders =
                item.type === TYPE_FOLDER && possible_sibling.type === TYPE_FOLDER;
            const are_both_documents =
                item.type !== TYPE_FOLDER && possible_sibling.type !== TYPE_FOLDER;
            if (are_both_folders || are_both_documents) {
                if (
                    possible_sibling.title.localeCompare(item.title, undefined, {
                        numeric: true
                    }) >= 0
                ) {
                    break;
                }
            } else if (item.type === TYPE_FOLDER) {
                break;
            }
            if (!next_sibling) {
                i++;
                break;
            }
            const are_next_item_and_current_item_sibling =
                next_sibling.parent_id === item.parent_id;
            if (!are_next_item_and_current_item_sibling) {
                if (next_sibling.parent_id === possible_sibling.parent_id) {
                    i++;
                    break;
                }
                const are_both_items_different_type =
                    possible_sibling.type === TYPE_FOLDER && item.type !== TYPE_FOLDER;
                if (are_both_items_different_type) {
                    i++;
                    continue;
                }
                i++;
                break;
            }
        }
        const is_current_folder_empty =
            next_sibling &&
            possible_sibling.id === item.parent_id &&
            next_sibling.parent_id === possible_sibling.parent_id;

        if (is_current_folder_empty) {
            i++;
            break;
        }
        i++;
    }

    state.folder_content.splice(i, 0, item);
}

function appendSubFolderContent(state, [folder_id, sub_items]) {
    const folder_index = state.folder_content.findIndex(folder => folder.id === folder_id);
    const parent_folder = state.folder_content[folder_index];

    if (!parent_folder.level) {
        parent_folder.level = 0;
    }

    sub_items.forEach(item => {
        item.level = parent_folder.level + 1;
    });

    state.folder_content.splice(folder_index + 1, 0, ...sub_items);
}

function foldFolderContent(state, folder_id) {
    const index = state.folder_content.findIndex(item => item.id === folder_id);

    if (index !== -1) {
        state.folder_content[index].is_expanded = false;
    }
    const children = getFolderUnfoldedDescendants(state, folder_id);
    const folded_content = children.map(item => item.id);

    state.folded_items_ids = state.folded_items_ids.concat(folded_content);

    state.folded_by_map[folder_id] = folded_content;
}

function unfoldFolderContent(state, folder_id) {
    const index = state.folder_content.findIndex(item => item.id === folder_id);

    if (index !== -1) {
        state.folder_content[index].is_expanded = true;
    }

    const items_to_unfold = state.folded_by_map[folder_id];

    if (!items_to_unfold) {
        return;
    }

    state.folded_items_ids = state.folded_items_ids.filter(item => !items_to_unfold.includes(item));

    delete state.folded_by_map[folder_id];
}

function resetFoldedLists(state) {
    state.folded_items_ids = [];
    state.folded_by_map = {};
}

function initApp(
    state,
    [
        user_id,
        project_id,
        user_is_admin,
        date_time_format,
        root_title,
        user_can_create_wiki,
        max_files_dragndrop,
        max_size_upload,
        is_under_construction
    ]
) {
    state.user_id = user_id;
    state.project_id = project_id;
    state.is_user_administrator = user_is_admin;
    state.date_time_format = date_time_format;
    state.root_title = root_title;
    state.user_can_create_wiki = user_can_create_wiki;
    state.max_files_dragndrop = max_files_dragndrop;
    state.max_size_upload = max_size_upload;
    state.is_under_construction = is_under_construction;
}

function saveAscendantHierarchy(state, hierarchy) {
    state.current_folder_ascendant_hierarchy = hierarchy;
}

function resetAscendantHierarchy(state) {
    state.current_folder_ascendant_hierarchy = [];
}

function beginLoading(state) {
    state.is_loading_folder = true;
}

function stopLoading(state) {
    state.is_loading_folder = false;
}

function beginLoadingAscendantHierarchy(state) {
    state.is_loading_ascendant_hierarchy = true;
}

function stopLoadingAscendantHierarchy(state) {
    state.is_loading_ascendant_hierarchy = false;
}

function resetErrors(state) {
    state.has_folder_permission_error = false;
    state.has_folder_loading_error = false;
    state.folder_loading_error = null;
}

function switchFolderPermissionError(state) {
    state.has_folder_permission_error = true;
}

function setFolderLoadingError(state, message) {
    state.has_folder_loading_error = true;
    state.folder_loading_error = message;
}

function appendFolderToAscendantHierarchy(state, folder) {
    const parent_index_in_hierarchy = state.current_folder_ascendant_hierarchy.findIndex(
        item => item.id === folder.parent_id
    );

    if (parent_index_in_hierarchy !== -1) {
        state.current_folder_ascendant_hierarchy.push(folder);
        return;
    }

    const folder_index = state.folder_content.findIndex(item => item.id === folder.id);
    const ascendants = state.folder_content.slice(0, folder_index);

    let next_parent_id = folder.parent_id;

    const direct_ascendants = ascendants.reduceRight((accumulator, item) => {
        if (item.id === next_parent_id) {
            accumulator.push(item);

            next_parent_id = item.parent_id;
        }

        return accumulator;
    }, []);

    state.current_folder_ascendant_hierarchy.push(...direct_ascendants.reverse(), folder);
}

function setCurrentFolder(state, folder) {
    state.current_folder = folder;
}

function getFolderUnfoldedDescendants(state, folder_id) {
    const children = state.folder_content.filter(item => item.parent_id === folder_id);

    const unfolded_descendants = [];

    children.forEach(child => {
        if (state.folded_by_map.hasOwnProperty(child.id)) {
            return;
        }

        unfolded_descendants.push(...getFolderUnfoldedDescendants(state, child.id));
    });

    return children.concat(unfolded_descendants);
}

function setModalError(state, error_message) {
    state.has_modal_error = true;
    state.modal_error = error_message;
}

function resetModalError(state) {
    state.has_modal_error = false;
    state.modal_error = null;
}

function removeCreatedPropertyOnItem(state, item) {
    Vue.delete(item, "created");
}

function replaceUploadingFileWithActualFile(state, [uploading_file, actual_file]) {
    const index = state.folder_content.findIndex(item => item.id === uploading_file.id);
    if (index === -1) {
        return;
    }

    state.folder_content.splice(index, 1, actual_file);
}

function removeItemFromFolderContent(state, item_to_remove) {
    const index = state.folder_content.findIndex(item => item.id === item_to_remove.id);
    if (index === -1) {
        return;
    }

    state.folder_content.splice(index, 1);
}

function removeIsUnderConstruction(state) {
    state.is_under_construction = false;
}

function addFileInUploadsList(state, file) {
    removeFileFromUploadsList(state, file);
    state.files_uploads_list.unshift(file);
}

function removeFileFromUploadsList(state, uploaded_file) {
    const file_index = state.files_uploads_list.findIndex(file => file.id === uploaded_file.id);
    if (file_index === -1) {
        return;
    }

    state.files_uploads_list.splice(file_index, 1);
}

function emptyFilesUploadsList(state) {
    state.files_uploads_list = [];
}
