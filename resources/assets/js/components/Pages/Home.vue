<template>
    <div slot="section" class="vld-parent">
        <loading :active.sync="isLoading"
                 :is-full-page="true"
                 :width="255"
                 :height="255"></loading>
        <div class="tab-pane" id="">
            <form autocomplete="off">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <div v-if="matrixShowFlag == false" style="max-width: 1000px; margin-left: auto; margin-right: auto;">
                                <h3><b>Set up your matrix</b></h3>
                            </div>
                        </div>
                        <div class="col-md-12" v-if="collapsedFlag == false">
                            <div style="max-width: 1000px; margin-left: auto; margin-right: auto; margin-top:50px;">
                                <div>
                                    <b>I 'm measuring <input class="" v-model="taxonName" style="width: 330px;"
                                                             placeholder="Carex capitata"/>.</b>
                                </div>
                                <div class="margin-top-10">
                                    <b>I have <input v-model="columnCount" style="width: 180px;" placeholder="Enter a default count, like 3"> specimens.</b>
                                </div>
                                <div class="margin-top-10 row">
                                    <div class="col-md-12" style="line-height: 38px;">
                                        <b>I 'm measuring <a class="btn btn-primary" v-on:click="showStandardCharacters()" v-tooltip="standardCharactersTooltip">
                                            the standard set of characters
                                        </a> <a style="cursor: pointer;" v-tooltip="standardCharactersTooltip"> ? </a> or</b>
                                    </div>
                                    <div class="col-md-12 margin-top-10">
                                        <model-select :options="standardCharacters"
                                                      v-model="item"
                                                      placeholder="Search character here"
                                                      @searchchange="printSearchText"
                                                      @select="onSelect"
                                        />

                                    </div>
                                </div>
                                <div class="margin-top-10" v-if="userCharacters.find(ch => ch.standard == 0)">
                                    <h4><b>Characters selected</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        <a class="btn btn-add display-block" v-on:click="removeAllCharacters()"><span
                                                class="glyphicon glyphicon-remove"></span></a>
                                    </h4>
                                    <div v-for="eachCharacter in userCharacters" v-if="eachCharacter.standard == 0" v-tooltip="eachCharacter.tooltip"
                                         style="display: table; font-weight: bold; cursor: pointer;">
                                        {{ eachCharacter.name }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        <a class="btn btn-add display-block"
                                           v-on:click="removeUserCharacter(eachCharacter.id)"><span
                                                class="glyphicon glyphicon-remove"></span></a>
                                        <!--<a-->
                                                <!--v-on:click="removeUserCharacter(eachCharacter.id)" style="cursor: pointer;">-->
                                            <!--X </a>-->
                                    </div>
                                </div>
                                <div class="margin-top-10" v-if="userCharacters.find(ch => ch.standard == 1)">
                                    <h4><b>Standard Characters&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b>
                                        <a class="btn btn-add display-block"
                                               v-on:click="removeAllStandardCharacters()"><span
                                            class="glyphicon glyphicon-remove"></span></a></h4>

                                    <div v-for="eachCharacter in userCharacters" v-if="eachCharacter.standard == 1" v-tooltip="eachCharacter.tooltip"
                                         style="display: table; cursor: pointer;">
                                        <b>{{ eachCharacter.name }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                            <a class="btn btn-add display-block"
                                               v-on:click="removeStandardCharacter(eachCharacter.id)"><span
                                                    class="glyphicon glyphicon-remove"></span></a></b>
                                            <!--<a-->
                                                    <!--v-on:click="removeStandardCharacter(eachCharacter.id)" style="cursor: pointer;">-->
                                                <!--X </a>-->
                                    </div>
                                </div>
                                <div class="margin-top-10 text-right">
                                    <a class="btn btn-primary" v-on:click="generateMatrix()" style="width: 200px;">Generate Matrix</a>
                                    <a class="btn btn-primary" v-on:click="importMatrix()" style="width: 200px; background-color: grey; border-color: grey;">Import (CR) Matrix</a>
                                    <a class="btn btn-primary" v-on:click="collapsedFlag = true;" style="width: 40px;"><span class="glyphicon glyphicon-chevron-up"></span></a>
                                </div>
                            </div>
                        </div>
                        <div v-if="collapsedFlag == true">
                            <div style="max-width: 1000px; margin-right: auto; margin-left: auto;">
                                <div class="col-md-1">
                                    <a class="btn btn-primary" v-on:click="collapsedFlag = false;" style="width: 40px;"><span class="glyphicon glyphicon-chevron-down"></span></a>
                                </div>
                                <div class="col-md-2">
                                    <input v-model="taxonName" v-on:blur="changeTaxonName()" style="line-height: 38px; border: none;">
                                </div>
                                <div class="col-md-3">
                                    <input v-model="columnCount" v-on:keyup.enter="changeColumnCount()" v-on:blur="changeColumnCount()" style="width: 40px; margin-left: 30px; line-height: 38px; border:none;"> Specimens
                                </div>
                                <div class="col-md-5">
                                    <model-select :options="standardCharacters"
                                                  v-model="item"
                                                  placeholder="Search character here"
                                                  @searchchange="printSearchText"
                                                  @select="onSelect"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <hr v-if="matrixShowFlag == true" style="margin-top: 40px; margin-bottom: 40px; border-top: 2px solid;">

                <div class="container">
                    <div class="row">
                        <div class="margin-top-10 col-md-12" v-if="matrixShowFlag == true">
                            <ul class="nav nav-tabs">
                                <li v-for="eachTag in userTags"><a data-toggle="tab" v-on:click="showTableForTab(eachTag.tag_name)">{{ eachTag.tag_name }}</a></li>
                            </ul>
                            <div style="overflow-x: auto; max-height: 700px; margin-right: auto; margin-left: auto;">
                                <table class="table table-bordered table-responsive cr-table">
                                    <thead>
                                    <tr>
                                        <th style="min-width: 270px; height: 43px; line-height: 43px; text-align: center;">
                                            Character
                                        </th>
                                        <th v-if="header.id != 1" v-for="header in headers" style="min-width: 200px;">
                                            <input class="th-input" v-bind:value="header.header"/>
                                            <a class="btn btn-add display-block"
                                               v-on:click="deleteHeader(header.id)"><span
                                                    class="glyphicon glyphicon-remove"></span></a>
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="row in values"
                                        v-if="userCharacters.find(ch => ch.id == row[0].character_id).show_flag == true">
                                        <td v-if="value.header_id == 1"
                                            v-for="value in row"
                                            v-tooltip="userCharacters.find(ch => ch.id == value.character_id).tooltip"
                                            style="cursor: pointer; line-height: 44px;">
                                            {{ value.value }}
                                        </td>
                                        <td v-if="value.header_id != 1" v-for="value in row">
                                            <input class="td-input" v-model="value.value"
                                                   v-on:blur="saveItem($event, value)"/>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>

                        </div>
                        <div v-if="newCharacterFlag" @close="newCharacterFlag = false">
                            <transition name="modal">
                                <div class="modal-mask character-modal">
                                    <div class="modal-wrapper">
                                        <div class="modal-container">
                                            <div class="modal-header">
                                                Input the character name in the input box and click OK.
                                            </div>
                                            <div class="modal-body">
                                                <b>Form character name:</b>
                                                <br>
                                                <br>
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <select v-model="firstCharacter" style="height: 26px;">
                                                            <option>length</option>
                                                            <option>width</option>
                                                            <option>depth</option>
                                                            <option>diameter</option>
                                                            <option>distance</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <select v-model="middleCharacter" style="height: 26px;">
                                                            <option>of</option>
                                                            <option>between</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <input v-model="lastCharacter">
                                                    </div>

                                                    <!--<input autofocus v-model="character.name" v-on:input="checkMsg"/>-->
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <a class="btn btn-primary ok-btn"
                                                   v-bind:class="{ disabled: !firstCharacter || !middleCharacter || !lastCharacter }"
                                                   v-on:click="storeCharacter()">
                                                    &nbsp; &nbsp; OK &nbsp; &nbsp; </a>
                                                <a v-on:click="cancelNewCharacter()" class="btn btn-danger">Cancel</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </transition>
                        </div>
                        <div v-if="detailsFlag" @close="detailsFlag = false">
                            <transition name="modal">
                                <div class="modal-mask">
                                    <div class="modal-wrapper">
                                        <div class="modal-container">

                                            <div class="modal-header">
                                                <h3>Information about "{{ character.name }}" by {{ character.username
                                                    }}</h3>
                                            </div>

                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-6 radial-menu">
                                                        <ul style="margin-left: auto; margin-right: auto;">
                                                            <li><a v-on:click="showDetails('', metadataFlag)"></a></li>
                                                            <li class="method"><a
                                                                    v-on:click="showDetails('method', metadataFlag)">1.
                                                                Method<br><span class="glyphicon glyphicon-edit"></span></a>
                                                            </li>
                                                            <li class="unit"><a
                                                                    v-on:click="showDetails('unit', metadataFlag)">2.
                                                                Unit<br><span class="glyphicon glyphicon-edit"></span></a>
                                                            </li>
                                                            <li class="tag"><a
                                                                    v-on:click="showDetails('tag', metadataFlag)">
                                                                Tag</a>
                                                            </li>
                                                            <li class="summary"><a
                                                                    v-on:click="showDetails('summary', metadataFlag)">
                                                                Summary<br>Function</a>
                                                            </li>
                                                            <li class="creator"><a
                                                                    v-on:click="showDetails('creator', metadataFlag)">Creator</a>
                                                            </li>
                                                            <li><a v-on:click="showDetails('usage', metadataFlag)">Usage</a>
                                                            </li>
                                                            <li>
                                                                <a v-on:click="showDetails('history', metadataFlag)">History</a>
                                                            </li>
                                                            <li><a v-on:click="showDetails('', metadataFlag)"></a></li>
                                                        </ul>
                                                        <div class="center">
                                                            <a>{{ character.name }}</a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div id="metadataPlace">
                                                            <div :is="currentMetadata" :parentData="parentData"
                                                                 @interface="handleFcAfterDateBack">

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12 text-right" style="margin-top: 15px;">
                                                        <a v-if="viewFlag == false"
                                                           v-on:click="saveCharacter(metadataFlag)" class="btn btn-primary">Save</a>
                                                        <a v-if="viewFlag == true" v-on:click="use(item)"
                                                           class="btn btn-primary">Use this</a>
                                                        <a v-if="viewFlag == true" v-on:click="enhance(item)"
                                                           class="btn btn-primary">Clone and enhance</a>
                                                        <a v-on:click="cancelCharacter()" class="btn btn-danger">Cancel</a>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="modal-footer">

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </transition>
                        </div>
                        <div v-if="confirmMethod" @close="confirmMethod = false">
                            <transition name="modal">
                                <div class="modal-mask character-modal">
                                    <div class="modal-wrapper">
                                        <div class="modal-container">
                                            <div class="modal-header">
                                                Confirm Method
                                            </div>
                                            <div class="modal-body">
                                                <div v-if="!character.method_as">
                                                    <div>
                                                        <b>Please review the method definition carefully. Is this what you would
                                                            like
                                                            to save for <i>{{ character.name }}</i>?</b>
                                                    </div>
                                                    <br>
                                                    <div v-if="character.method_from">
                                                        From: {{ character.method_from }}
                                                    </div>
                                                    <div v-if="character.method_to">
                                                        To: {{ character.method_to }}
                                                    </div>
                                                    <div v-if="character.method_include">
                                                        Include: {{ character.method_include }}
                                                    </div>
                                                    <div v-if="character.method_exclude">
                                                        Exclude: {{ character.method_exclude }}
                                                    </div>
                                                    <div v-if="character.method_where">
                                                        Where: {{ character.method_where }}
                                                    </div>
                                                </div>
                                                <div v-if="character.method_as">
                                                    <div>
                                                        <b>Please review the method definition carefully. Is this what you
                                                            would like
                                                            to save for <i>{{ character.name }}</i>?</b>
                                                    </div>
                                                    <div>
                                                        <img class="img-method"
                                                             style="width: 100%;"
                                                             v-bind:src="'https://drive.google.com/uc?id=' + character.method_as.split('id=')[1].substring(0, character.method_as.split('id=')[1].length)"/>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <a class="btn btn-primary ok-btn"
                                                       v-on:click="methodConfirm()">
                                                        &nbsp; &nbsp; Confirm &nbsp; &nbsp; </a>
                                                    <a v-on:click="cancelConfirmMethod()" class="btn btn-danger">Cancel</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </transition>
                        </div>
                        <div v-if="confirmUnit" @close="confirmUnit = false">
                            <transition name="modal">
                                <div class="modal-mask character-modal">
                                    <div class="modal-wrapper">
                                        <div class="modal-container">
                                            <div class="modal-header">
                                                Confirm Unit
                                            </div>
                                            <div class="modal-body">
                                                <div>
                                                    You've select <b>{{ character.unit }}</b> as the Unit for <i>{{ character.name }}</i>.
                                                </div>
                                                <div class="modal-footer">
                                                    <a class="btn btn-primary ok-btn"
                                                       v-on:click="confirmSave(metadataFlag)">
                                                        &nbsp; &nbsp; Confirm &nbsp; &nbsp; </a>
                                                    <a v-on:click="cancelConfirmUnit()" class="btn btn-danger">Cancel</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </transition>
                        </div>
                    </div>

                </div>


            </form>
        </div>
    </div>

</template>

<script>
    import Vue from 'vue';

    import method from '../Metadata/method.vue';
    import unit from '../Metadata/unit.vue';
    import tag from '../Metadata/tag.vue';
    import summary from '../Metadata/summary.vue';
    import creator from '../Metadata/creator.vue';
    import usage from '../Metadata/usage.vue';
    import history from '../Metadata/history.vue';
    
    import {ModelSelect} from '../../libs/vue-search-select-lib'
    Vue.use({ModelSelect});

    import Loading from 'vue-loading-overlay';
    import 'vue-loading-overlay/dist/vue-loading.css';

    export default {
        props: [
            'user',
        ],
        data: function () {
            return {
                character: {},
                userCharacters: [],
                defaultCharacters: [],
                standardCharacters: [],
                standardShowCharacters: [],
                item: null,
                searchText: null,
                standardShowFlag: false,
                firstCharacter: null,
                middleCharacter: null,
                lastCharacter: null,
                newCharacterFlag: false,
                detailsFlag: false,
                metadataFlag: null,
                currentMetadata: null,
                parentData: null,
                viewFlag: false,
                standardCharactersTooltip: '',
                confirmMethod: false,
                confirmUnit: false,
                columnCount: 0,
//                taxonName: '',
                taxonName: 'Carex capitata',
                matrixShowFlag: false,
                headers : [],
                values : [],
                collapsedFlag: false,
                value: {
                    value: ''
                },
                isLoading: false,
                userTags: [],
            }
        },
        components: {
            ModelSelect,
            Loading
        },

        methods: {
            handleFcAfterDateBack (event) {
                console.log('hadleFcAfterDateBack function inited');
                var app = this;
                app.updatedFlag = true;
                $('.center').addClass('back-yellow');
                $('.' + app.metadataFlag).addClass('back-median-green');
                console.log("app.metadataFlag", app.metadataFlag);
                switch (app.metadataFlag) {
                    case 'method':
                        app.character.method_as = event[0];
                        app.term = event[1];
                        app.termValue = event[2];
                        app.character.method_from = event[4];
                        app.character.method_to = event[5];
                        app.character.method_include = event[6];
                        app.character.method_exclude = event[7];
                        app.character.method_where = event[8];
                        app.parentData = event;
                        app.methodUpdateFlag = true;
                        console.log("method return", event);
                        break;
                    case 'unit':
                        app.character.unit = event;
                        app.parentData = event;
                        app.unitUpdateFlag = true;
                        break;
                    case 'tag':
                        app.character.standard_tag = event;
                        app.parentData = event;
                        break;
                    case 'summary':
                        app.character.summary = event;
                        app.parentData = event;
                        break;
                    case 'creator':
                        app.character.creator = event;
                        app.parentData = event;
                        app.creatorUpdateFlag = true;
                        break;
                    default:
                        break;
                }
                console.log('app.character after handle: ', app.character); // get the data after child dealing
            },
            printSearchText (searchText) {
                app.searchText = searchText;
            },
            onSelect(selectedItem) {
                var app = this;
                var selectedCharacter = app.defaultCharacters.find(ch => ch.id == selectedItem);
                if (!selectedCharacter) {
                    app.firstCharacter = '';
                    app.middleCharacter = '';
                    app.lastCharacter = '';

                    app.newCharacterFlag = true;
                    app.viewFlag = false;
                    sessionStorage.setItem('viewFlag', false);
                    sessionStorage.setItem('edit_created_other', false);
                } else {
                    app.character = selectedCharacter;
                    app.item = selectedItem;
                    app.viewFlag = true;
                    sessionStorage.setItem('viewFlag', true);
                    sessionStorage.setItem('edit_created_other', true);
                    app.editCharacter(app.character);
                }
                console.log('selectedCharacter', selectedCharacter);
            },
            editCharacter(character) {
                var app = this;
                sessionStorage.setItem("characterName", character.name);
                app.parentData = [];
                app.parentData.push(app.character.method_as);
                app.parentData[3] = app.user;
                app.parentData[4] = app.character.method_from;
                app.parentData[5] = app.character.method_to;
                app.parentData[6] = app.character.method_include;
                app.parentData[7] = app.character.method_exclude;
                app.parentData[8] = app.character.method_where;
                app.metadataFlag = 'method';
                app.currentMetadata = method;
                app.detailsFlag = true;
            },
            storeCharacter() {
                var app = this;
                app.character = {};
                app.character.name = app.firstCharacter + ' ' + app.middleCharacter + ' ' + app.lastCharacter;
                app.character.username = app.user.name;
                app.character.creator = app.user.name + ' via CR';
                app.parentData = [];
                app.parentData[3] = app.user;
                app.metadataFlag = 'method';
                app.currentMetadata = method;
                sessionStorage.setItem("characterName", app.character.name);
                console.log("parentData", app.parentData);
                app.newCharacterFlag = false;
                app.detailsFlag = true;
            },
            cancelNewCharacter() {
                var app = this;
                app.newCharacterFlag = false;
            },
            cancelCharacter() {
                var app = this;
                app.detailsFlag = false;
            },
            showDetails(metadata, previousMetadata = null) {
                var app = this;

                console.log("metadata", metadata);
                console.log("app.character=", app.character);
                app.metadataFlag = metadata;
                switch (metadata) {
                    case 'method':
                        app.parentData = [];
                        app.parentData[0] = app.character.method_as;
                        app.parentData[3] = app.user;
                        app.parentData[4] = app.character.method_from;
                        app.parentData[5] = app.character.method_to;
                        app.parentData[6] = app.character.method_include;
                        app.parentData[7] = app.character.method_exclude;
                        app.parentData[8] = app.character.method_where;
                        app.currentMetadata = method;
                        break;
                    case 'unit':
                        app.parentData = app.character.unit;
                        app.currentMetadata = unit;
                        break;
                    case 'tag':
                        app.parentData = app.character.standard_tag;
                        app.currentMetadata = tag;
                        break;
                    case 'summary':
                        app.parentData = app.character.summary;
                        app.currentMetadata = summary;
                        break;
                    case 'creator':
                        app.parentData = app.character.username + ' via CR';//app.character.creator;
                        app.currentMetadata = creator;
                        break;
                    case 'usage':
                        app.parentData = app.character.usage;
                        app.currentMetadata = usage;
                        break;
                    case 'history':
                        app.parentData = app.character.history;
                        app.currentMetadata = history;
                        break;
                    default:
                        break;
                }
            },
            showStandardCharacters() {
                var app = this;
                app.isLoading = true;
                app.standardShowFlag = !app.standardShowFlag;
                var postCharacters = [];
                for (var i = 0; i < app.defaultCharacters.length; i++) {
                    var character = app.defaultCharacters[i];
                    if (!app.userCharacters.find(ch => ch.name == character.name)) {
                        character.username = app.user.name;
                        character.show_flag = false;
                        postCharacters.push(character);
                    }
                }

                axios.post('/chrecorder/public/api/v1/character/add-standard', postCharacters)
                    .then(function(resp) {
                        console.log('addStandard resp', resp.data);
                        app.userCharacters = resp.data;
                        app.isLoading = false;
                        app.refreshUserCharacters();
                    });
            },
            removeStandardCharacter(characterId) {
                var app = this;

                axios.post("/chrecorder/public/api/v1/character/delete/" + app.user.id + "/" + characterId)
                    .then(function(resp) {
                        app.userCharacters = resp.data;
                        app.refreshUserCharacters();

                    });

            },
            removeUserCharacter(characterId) {
                var app = this;
                axios.post("/chrecorder/public/api/v1/character/delete/" + app.user.id + "/" + characterId)
                    .then(function(resp) {
                        var oldUserTag = app.userCharacters.find(ch => ch.id == characterId).standard_tag;
                        app.userCharacters = resp.data;
                        if (!app.userCharacters.find(ch => ch.standard_tag == oldUserTag)) {
                            var jsonUserTag = {
                                user_id: app.user.id,
                                user_tag: oldUserTag
                            };
                            console.log('remove jsonUserTag', jsonUserTag);
                            axios.post("/chrecorder/public/api/v1/user-tag/remove", jsonUserTag)
                                .then(function(resp) {
                                    console.log("remove UserTag", resp.data);
                                });
                        }
                        app.refreshUserCharacters();

                    });
            },
            removeAllCharacters() {

            },
            saveCharacter(metadataFlag) {
                var app = this;
                console.log('app.character = ', app.character);
                console.log('saveCharacter', metadataFlag);
                if (app.character['id']) {
                    delete app.character['id'];
                }

                var checkFields = true;
                if ((this.character['method_as'] == null || this.character['method_as'] == '') &&
                    (this.character['method_from'] == null || this.character['method_from'] == '') &&
                    (this.character['method_to'] == null || this.character['method_to'] == '') &&
                    (this.character['method_include'] == null || this.character['method_include'] == '') &&
                    (this.character['method_exclude'] == null || this.character['method_exclude'] == '') &&
                    (this.character['method_where'] == null || this.character['method_where'] == '')) {
                    checkFields = false;
                }

                if (!app.character['unit']) {
                    checkFields = false;
                }

                if (checkFields) {
                    app.confirmMethod = true;
                } else {
                    app.showDetails('unit', app.metadataFlag);
                }
            },
            use(characterId) {
                var app = this;
                app.character = app.defaultCharacters.find(ch => ch.id == characterId);
                console.log('use', characterId);

                var checkFields = true;
                if ((this.character['method_as'] == null || this.character['method_as'] == '') &&
                    (this.character['method_from'] == null || this.character['method_from'] == '') &&
                    (this.character['method_to'] == null || this.character['method_to'] == '') &&
                    (this.character['method_include'] == null || this.character['method_include'] == '') &&
                    (this.character['method_exclude'] == null || this.character['method_exclude'] == '') &&
                    (this.character['method_where'] == null || this.character['method_where'] == '')) {
                    checkFields = false;
                }

                if (!app.character['unit']) {
                    checkFields = false;
                }

                if (checkFields) {
                    if (app.character['id']) {
                        delete app.character['id'];
                    }
                    app.confirmMethod = true;
                } else {
                    app.showDetails('unit', app.metadataFlag);
                }
            },
            enhance(characterId) {
                var app = this;
                var selectedCharacter = app.defaultCharacters.find(ch => ch.id == characterId);
                selectedCharacter.username = app.user.name;
                selectedCharacter.creator = app.user.name + ' via CR';
                app.detailsFlag = false;
                sessionStorage.setItem('viewFlag', false);
                sessionStorage.setItem('edit_created_other', false);
                app.viewFlag = false;
                setTimeout(function() { app.editCharacter(selectedCharacter); }, 1);
            },
            methodConfirm() {
                var app = this;
                app.confirmMethod = false;
                app.confirmUnit = true;
            },
            cancelConfirmMethod() {
                var app = this;
                app.confirmMethod = false;
            },
            cancelConfirmUnit() {
                var app = this;
                app.confirmUnit = false;
            },
            checkUserTag(userTag) {
                var app = this;

            },
            confirmSave(metadataFlag) {
                var app = this;
                var userId = sessionStorage.getItem("userId");
                app.confirmUnit = false;
                axios.get("/chrecorder/public/api/v1/character/" + userId)
                    .then(function(resp) {
                        console.log('getCharacter', resp);
                        var currentCharacters = resp.data.characters;
                        if (currentCharacters.find(ch => ch.name == app.character.name)) {
                            alert("The character already exists for this user!!");
                        } else {
                            app.character.standard = 0;
                            app.character.show_flag = false;
                            if (app.matrixShowFlag) {
                                axios.post('api/v1/character/add-character', app.character)
                                    .then(function(resp) {
                                        if (!app.userCharacters.find(ch => ch.standard_tag == app.character.standard_tag)) {
                                            var jsonUserTag = {
                                                user_id: app.user.id,
                                                user_tag: app.character.standard_tag
                                            };
                                            console.log('jsonUserTag', jsonUserTag);
                                            axios.post("/chrecorder/public/api/v1/user-tag/create", jsonUserTag)
                                                .then(function(resp) {
                                                    axios.get('/chrecorder/public/api/v1/user-tag/' + app.user.id)
                                                        .then(function(resp) {
                                                            app.userTags = resp.data;
                                                        });
                                                    console.log("create UserTag", resp.data);
                                                });
                                        } else {
                                            axios.get('/chrecorder/public/api/v1/user-tag/' + app.user.id)
                                                .then(function(resp) {
                                                    app.userTags = resp.data;
                                                });
                                        }

                                        app.userCharacters = resp.data.characters;
                                        app.headers = resp.data.headers;
                                        app.values = resp.data.values;
                                        app.taxonName = resp.data.taxon;

                                        app.refreshUserCharacters();

                                        app.detailsFlag = false;
                                    });
                            } else {
                                axios.post("/chrecorder/public/api/v1/character/create", app.character)
                                    .then(function(resp) {
                                        if (!app.userCharacters.find(ch => ch.standard_tag == app.character.standard_tag)) {
                                            var jsonUserTag = {
                                                user_id: app.user.id,
                                                user_tag: app.character.standard_tag
                                            };
                                            console.log('jsonUserTag', jsonUserTag);
                                            axios.post("/chrecorder/public/api/v1/user-tag/create", jsonUserTag)
                                                .then(function(resp) {
                                                    console.log("create UserTag", resp.data);
                                                });
                                        }
                                        app.userCharacters = resp.data;
                                        app.refreshUserCharacters();

                                        app.detailsFlag = false;
                                    });
                            }
                        }
                    });
                console.log("app.character", app.character);
            },
            generateMatrix() {
                var app = this;
                app.isLoading = true;
                if ((isNaN(app.columnCount) == false) && app.columnCount > 0 && app.taxonName != "") {
                    var jsonMatrix = {
                        'user_id': app.user.id,
                        'column_count': app.columnCount,
                        'taxon': app.taxonName
                    };
                    axios.post('/chrecorder/public/api/v1/matrix-store', jsonMatrix)
                        .then(function(resp) {
                            app.isLoading = false;
                            console.log('resp storeMatrix', resp.data);
                            app.matrixShowFlag = true;
                            app.collapsedFlag = true;
                            app.userCharacters = resp.data.characters;
                            app.headers = resp.data.headers;
                            app.values = resp.data.values;

                            app.refreshUserCharacters();

                            console.log('userCharacters', app.userCharacters);
                        });
                    axios.get('/chrecorder/public/api/v1/user-tag/' + app.user.id)
                        .then(function(resp) {
                            app.userTags = resp.data;
                        });
                } else {
                    app.isLoading = false;
                    alert("You need to fill the taxon name and specimen count in the input box!")
                }

            },
            deleteHeader(headerId) {
                var app = this;
                app.isLoading = true;
                axios.post('/chrecorder/public/api/v1/delete-header/' + headerId)
                    .then(function(resp) {
                        console.log('delete header', resp.data);
                        app.headers = resp.data.headers;
                        app.values = resp.data.values;
                        app.userCharacters = resp.data.characters;
                        app.columnCount = resp.data.headers.length - 1;
                        app.isLoading = false;
                        app.refreshUserCharacters();

                    });
            },
            changeTaxonName() {
                var app = this;
                axios.post('/chrecorder/public/api/v1/change-taxon/' + app.taxonName)
                    .then(function(resp) {
                        app.taxonName = resp.data.taxon;
                    });
            },
            changeColumnCount() {
                var app = this;
                app.isLoading = true;
                if (app.columnCount < app.headers.length - 1) {
                    app.columnCount = app.headers.length - 1;
                    alert("To reduce the size of the matrix, use the remove button (x) in the matrix.");
                } else {
                    axios.post('/chrecorder/public/api/v1/add-more-column/' + app.columnCount)
                        .then(function(resp) {
                            console.log('addMoreColumn resp', resp.data);
                            app.userCharacters = resp.data.characters;
                            app.headers = resp.data.headers;
                            app.values = resp.data.values;
                            app.taxonName = resp.data.taxon;
                            app.isLoading = false;
                            app.refreshUserCharacters();

                        });
                }
            },
            saveItem(event, value) {
                var app = this;
                if (value.value < 0) {
                    alert("Value should be only positive value.");
                    value.value = '';
                } else {
                    axios.post('/chrecorder/public/api/v1/character/update', value)
                        .then(function(resp) {
                            console.log('saveItem', resp.data);
                            if (resp.data.error_input == 1) {
                                event.target.style.color = 'red';
                            } else {
                                event.target.style.color = 'black';
                                app.userCharacters = resp.data.characters;
                                app.headers = resp.data.headers;
                                app.values = resp.data.values;
                                app.refreshUserCharacters();
                            }
                        });
                }
            },
            removeAllStandardCharacters() {
                var app = this;
                app.isLoading = true;
                axios.post('/chrecorder/public/api/v1/character/remove-all-standard')
                    .then(function(resp) {
                        app.isLoading = false;
                        app.userCharacters = resp.data;
                        app.refreshUserCharacters();
                    });
            },
            refreshUserCharacters () {
                var app = this;
                for (var i = 0; i < app.userCharacters.length; i++) {
                    app.userCharacters[i].tooltip = '';
                    if (app.userCharacters[i].method_from != null && app.userCharacters[i].method_from != '') {
                        app.userCharacters[i].tooltip = app.userCharacters[i].tooltip + 'From: ' + app.userCharacters[i].method_from + ', ';
                    }
                    if (app.userCharacters[i].method_to != null && app.userCharacters[i].method_to != '') {
                        app.userCharacters[i].tooltip += 'To: ' + app.userCharacters[i].method_to + ', ';
                    }
                    if (app.userCharacters[i].method_include != null && app.userCharacters[i].method_include != '') {
                        app.userCharacters[i].tooltip += 'Include: ' + app.userCharacters[i].method_include + ', ';
                    }
                    if (app.userCharacters[i].method_exclude != null && app.userCharacters[i].method_exclude != '') {
                        app.userCharacters[i].tooltip += 'Exclude: ' + app.userCharacters[i].method_exclude + ', ';
                    }
                    if (app.userCharacters[i].method_where != null && app.userCharacters[i].method_where != '') {
                        app.userCharacters[i].tooltip += 'Where: ' + app.userCharacters[i].method_where;
                    }
                }
            },
            showTableForTab(tagName) {
                var app = this;
                app.isLoading = true;
                axios.post('/chrecorder/public/api/v1/show-tab-character/' + tagName)
                    .then(function(resp) {
                        app.isLoading = false;
                        app.userCharacters = resp.data.characters;
                        app.headers = resp.data.headers;
                        app.values = resp.data.values;
                        app.refreshUserCharacters();
                    });
//                app.hideAllCharacter();
//                var showCharacters = app.userCharacters.filter(ch => ch.standard_tag == tagName);
//                console.log('showCharacters', showCharacters);
//                for (var i = 0; i < showCharacters.length; i++) {
//                    app.userCharacters.find(ch => ch.id == showCharacters[i].id).show_flag = true;
//                }
            },
            hideAllCharacter() {
                var app = this;
                for (var i = 0; i < app.userCharacters.length; i++) {
                    app.userCharacters[i].show_flag = false;
                }
            },
            importMatrix() {

            },
        },
        created() {
            var app = this;
            axios.get('/chrecorder/public/api/v1/standard_characters')
                .then(function (resp) {
                    console.log('standardCharacters', resp);
                    app.defaultCharacters = resp.data;
                    for (var i = 0; i < resp.data.length; i++) {
                        var temp = {};
                        temp.name = resp.data[i].name;
                        app.standardCharactersTooltip = app.standardCharactersTooltip + resp.data[i].name + '<br/>';
                        temp.text = resp.data[i].name + ' by ' + resp.data[i].username + ' (' + resp.data[i].usage_count + ')';
                        temp.value = resp.data[i].id;
                        temp.tooltip = '';

                        if (resp.data[i].method_from != null && resp.data[i].method_from != '') {
                            temp.tooltip = temp.tooltip + 'From: ' + resp.data[i].method_from + ', ';
                        }
                        if (resp.data[i].method_to != null && resp.data[i].method_to != '') {
                            temp.tooltip = temp.tooltip + 'To: ' + resp.data[i].method_to + ', ';
                        }
                        if (resp.data[i].method_include != null && resp.data[i].method_include != '') {
                            temp.tooltip = temp.tooltip + 'Include: ' + resp.data[i].method_include + ', ';
                        }
                        if (resp.data[i].method_exclude != null && resp.data[i].method_exclude != '') {
                            temp.tooltip = temp.tooltip + 'Exclude: ' + resp.data[i].method_exclude + ', ';
                        }
                        if (resp.data[i].method_where != null && resp.data[i].method_where != '') {
                            temp.tooltip = temp.tooltip + 'Where: ' + resp.data[i].method_where;
                        }
                        app.standardShowCharacters.push(temp);
                        app.standardCharacters.push(temp);
                    }
                });
            axios.get("/chrecorder/public/api/v1/character/" + app.user.id)
                .then(function(resp) {
                    console.log('resp character', resp.data);
                    app.userCharacters = resp.data.characters;
                    app.headers = resp.data.headers;
                    app.values = resp.data.values;
                    app.taxonName = resp.data.taxon;
                    app.columnCount = resp.data.headers.length - 1;
                    if (app.columnCount == 0) {
                        app.columnCount = 3;
                    }
                    for (var i = 0; i < app.userCharacters.length; i++) {
                        app.userCharacters[i].tooltip = '';
                        if (app.userCharacters[i].method_from != null && app.userCharacters[i].method_from != '') {
                            app.userCharacters[i].tooltip = app.userCharacters[i].tooltip + 'From: ' + app.userCharacters[i].method_from + ', ';
                        }
                        if (app.userCharacters[i].method_to != null && app.userCharacters[i].method_to != '') {
                            app.userCharacters[i].tooltip += 'To: ' + app.userCharacters[i].method_to + ', ';
                        }
                        if (app.userCharacters[i].method_include != null && app.userCharacters[i].method_include != '') {
                            app.userCharacters[i].tooltip += 'Include: ' + app.userCharacters[i].method_include + ', ';
                        }
                        if (app.userCharacters[i].method_exclude != null && app.userCharacters[i].method_exclude != '') {
                            app.userCharacters[i].tooltip += 'Exclude: ' + app.userCharacters[i].method_exclude + ', ';
                        }
                        if (app.userCharacters[i].method_where != null && app.userCharacters[i].method_where != '') {
                            app.userCharacters[i].tooltip += 'Where: ' + app.userCharacters[i].method_where;
                        }

                    }
                });
            axios.get("/chrecorder/public/api/v1/user-tag/" + app.user.id)
                .then(function(resp) {
                    app.userTags = resp.data;
                    console.log('userTags', app.userTags);
                });
        },
        mounted() {
            var app = this;
            app.user.name = app.user.email.split('@')[0];
            sessionStorage.setItem('userId', app.user.id);
        },
    }

</script>