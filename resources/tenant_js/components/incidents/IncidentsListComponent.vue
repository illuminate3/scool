<template>
    <span>
        <v-toolbar color="blue darken-3">
            <v-menu bottom>
                <v-btn slot="activator" icon dark>
                    <v-icon>more_vert</v-icon>
                </v-btn>
                <v-list>
                    <v-list-tile href="/jobs/sheet_holders" target="_blank">
                        <v-list-tile-title>TODO 0 Estadístiques</v-list-tile-title>
                    </v-list-tile>
                    <v-list-tile href="/jobs/sheet_active_users" target="_blank">
                        <v-list-tile-title>TODO 1 Exportar a CSV/Excel</v-list-tile-title>
                        <!-- TODO-->
                    </v-list-tile>
                    <v-list-tile href="/changelog/module/incidents" target="_blank">
                        <v-list-tile-title>Mostrar historial incidències (registre de canvis)</v-list-tile-title>
                    </v-list-tile>
                </v-list>
            </v-menu>
            <v-toolbar-title class="white--text title">Incidències</v-toolbar-title>
            <v-spacer></v-spacer>

            <v-btn id="incidents_help_button" icon class="white--text" href="http://docs.scool.cat/docs/incidents" target="_blank">
                <v-icon>help</v-icon>
            </v-btn>

            <fullscreen-dialog
                    v-role="'IncidentsManager'"
                    :flat="false"
                    class="white--text"
                    icon="settings"
                    v-model="settingsDialog"
                    color="blue darken-3"
                    title="Canviar la configuració de les incidències">
                        <incident-settings module="incidents" @close="settingsDialog = false" :incident-users="incidentUsers" :manager-users="managerUsers"></incident-settings>
            </fullscreen-dialog>

            <v-btn id="incidents_refresh_button" icon class="white--text" @click="refresh" :loading="refreshing" :disabled="refreshing">
                <v-icon>refresh</v-icon>
            </v-btn>
        </v-toolbar>

        <v-card>
            <v-card-title>
                <v-layout>
                  <v-flex xs9 style="align-self: flex-end;">
                      <v-layout>
                          <v-flex xs3 class="text-sm-left" style="align-self: center;">
                                <span @click="showOpenIncidents" :class="{ bolder: filter === 'open', 'no-wrap': true, 'pointer': true }">
                                    <v-icon color="error" title="Obertes">lock_open</v-icon> Obertes: {{openIncidents ? openIncidents.length : 0}}
                                </span>
                                <span @click="showClosedIncidents" :class="{ bolder: filter === 'closed', 'no-wrap': true, 'pointer': true  }">
                                  <v-icon color="success" title="Tancades">lock</v-icon> Tancades: {{closedIncidents ? closedIncidents.length : 0}}
                                </span>
                                <span @click="showAll" :class="{ bolder: filter === 'all', 'no-wrap': true, 'pointer': true  }">
                                  <v-icon color="primary" title="Total">info</v-icon> Total: {{dataIncidents ? dataIncidents.length : 0}}
                                </span>
                          </v-flex>
                          <v-flex xs9>
                               <v-layout>
                                   <v-flex xs4>
                                       <user-select
                                               label="Creada per:"
                                               :users="creators"
                                               v-model="creator"
                                       ></user-select>
                                   </v-flex>
                                   <v-flex xs4>
                                       <user-select
                                               label="Assignada a:"
                                               :users="filteredAssignees"
                                               v-model="assignee"
                                       ></user-select>
                                   </v-flex>
                                   <v-flex xs4>
                                       <v-autocomplete
                                               v-model="selectedTags"
                                               :items="dataTags"
                                               attach
                                               chips
                                               label="Etiquetes"
                                               multiple
                                               item-value="id"
                                               item-text="value"
                                       >
                                            <template slot="selection" slot-scope="data">
                                                <v-chip
                                                        small
                                                        label
                                                        @input="data.parent.selectItem(data.item)"
                                                        :selected="data.selected"
                                                        class="chip--select-multi"
                                                        :color="data.item.color"
                                                        text-color="white"
                                                        :key="JSON.stringify(data.item)"
                                                ><v-icon small left v-text="data.item.icon"></v-icon>{{ data.item.value }}</v-chip>
                                            </template>
                                            <template slot="item" slot-scope="data">
                                                <v-checkbox v-model="data.tile.props.value"></v-checkbox>
                                                <v-chip small label :title="data.item.description" :color="data.item.color" text-color="white">
                                                    <v-icon small left v-text="data.item.icon"></v-icon>{{ data.item.value }}
                                                </v-chip>
                                            </template>
                                       </v-autocomplete>
                                   </v-flex>
                               </v-layout>
                          </v-flex>
                      </v-layout>
                  </v-flex>
                  <v-flex xs3>
                      <v-text-field
                              append-icon="search"
                              label="Buscar"
                              single-line
                              hide-details
                              v-model="search"
                      ></v-text-field>
                  </v-flex>
                </v-layout>
            </v-card-title>
            <v-data-table
                    class="px-0 mb-2 hidden-sm-and-down"
                    :headers="headers"
                    :items="filteredIncidents"
                    :search="search"
                    item-key="id"
                    no-results-text="No s'ha trobat cap registre coincident"
                    no-data-text="No hi han dades disponibles"
                    rows-per-page-text="Incidències per pàgina"
                    :rows-per-page-items="[5,10,25,50,100,200,{'text':'Tots','value':-1}]"
                    :pagination.sync="pagination"
                    :loading="refreshing"
            >
                <v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>
                <template slot="items" slot-scope="{item: incident}">
                    <tr :id="'incident_row_' + incident.id">
                        <td class="text-xs-left" v-text="incident.id"></td>
                        <td class="text-xs-left" :title="incident.user_email">
                            <user-avatar class="mr-2" :hash-id="incident.user_hashid"
                                         :alt="incident.user_name"
                                         v-if="incident.user_hashid"
                            ></user-avatar>
                            {{incident.user_name}}
                        </td>
                        <td class="text-xs-left">
                            <inline-text-field-edit-dialog v-model="incident" field="subject" label="Títol" @save="refresh"></inline-text-field-edit-dialog>
                        </td>
                        <td class="text-xs-left">
                            <inline-text-area-edit-dialog v-model="incident" :marked="false" field="description" label="Descripció" @save="refresh"></inline-text-area-edit-dialog>
                        </td>
                        <td v-if="filter!=='open'" class="text-xs-left" :title="incident.formatted_closed_at">
                            <template v-if="incident.formatted_closed_at">
                                <span :title="incident.formatted_closed_at">{{incident.formatted_closed_at_diff}}</span> per
                                <user-avatar :title="incident.closer_email" class="mr-2" :hash-id="incident.user_hashid"
                                             :alt="incident.closer_name"
                                             v-if="incident.closer_hashid"
                                ></user-avatar>
                            </template>
                            <template v-else>
                                No
                            </template>
                        </td>
                        <td class="text-xs-left">
                            <incident-tags @refresh="refresh(false)" :incident="incident" :tags="dataTags" ></incident-tags>
                        </td>
                        <td class="text-xs-left">
                            <incident-assignees @refresh="refresh" :assignees="incident.assignees" :incident="incident" :incident-users="incidentUsers"></incident-assignees>
                        </td>
                        <td class="text-xs-left" v-html="incident.formatted_created_at_diff" :title="incident.formatted_created_at"></td>
                        <td class="text-xs-left" :title="incident.formatted_updated_at">{{incident.formatted_updated_at_diff}}</td>
                        <td class="text-xs-left">
                            <changelog-loggable :loggable="incident"></changelog-loggable>
                            <fullscreen-dialog
                                    :badge="incident.comments && incident.comments.length"
                                    badge-color="teal"
                                    icon="chat_bubble_outline"
                                    color="teal"
                                    v-model="addCommentDialog"
                                    title="Afegir un comentari"
                                    :resource="incident"
                                    v-if="addCommentDialog === false || addCommentDialog === incident.id">
                                <incident-show :show-data="false" :incident="incident" v-role="'Incidents'" @close="addCommentDialog = false" :tags="dataTags" :incident-users="incidentUsers"></incident-show>
                            </fullscreen-dialog>
                            <fullscreen-dialog
                                    v-model="showDialog"
                                    title="Mostra la incidència"
                                    :resource="incident"
                                    v-if="showDialog === false || showDialog === incident.id">
                                <incident-show :incident="incident" v-role="'Incidents'" @close="showDialog = false" :tags="dataTags" :incident-users="incidentUsers"></incident-show>
                            </fullscreen-dialog>
                            <incident-close v-model="incident" v-if="$can('close',incident) || $hasRole('IncidentsManager')" @toggle="refresh"></incident-close>
                            <incident-delete :incident="incident" v-if="$hasRole('IncidentsManager')"></incident-delete>
                        </td>
                    </tr>
                </template>
            </v-data-table>
        </v-card>
    </span>
</template>

<script>
import * as actions from '../../store/action-types'
import * as mutations from '../../store/mutation-types'
import IncidentCloseComponent from './IncidentCloseComponent'
import IncidentShowComponent from './IncidentShowComponent'
import IncidentDeleteComponent from './IncidentDeleteComponent'
import IncidentTagsComponent from './IncidentTagsComponent'
import IncidentAssigneesComponent from './IncidentAssigneesComponent'
import IncidentSettings from './IncidentSettingsComponent'
import InlineTextFieldEditDialog from '../ui/InlineTextFieldEditDialog'
import InlineTextAreaEditDialog from '../ui/InlineTextAreaEditDialog'
import FullScreenDialog from '../ui/FullScreenDialog'
import UserSelect from '../users/UsersSelectComponent.vue'
import UserAvatar from '../ui/UserAvatarComponent'
import ChangelogLoggable from '../changelog/ChangelogLoggable'

var filters = {
  all: function (incidents) {
    return incidents
  },
  open: function (incidents) {
    return incidents ? incidents.filter(function (incident) {
      return incident.closed_at === null
    }) : []
  },
  closed: function (incidents) {
    return incidents ? incidents.filter(function (incident) {
      return incident.closed_at !== null
    }) : []
  }
}

export default {
  name: 'IncidentsList',
  components: {
    'fullscreen-dialog': FullScreenDialog,
    'incident-show': IncidentShowComponent,
    'incident-close': IncidentCloseComponent,
    'incident-delete': IncidentDeleteComponent,
    'inline-text-field-edit-dialog': InlineTextFieldEditDialog,
    'inline-text-area-edit-dialog': InlineTextAreaEditDialog,
    'user-select': UserSelect,
    'user-avatar': UserAvatar,
    'incident-settings': IncidentSettings,
    'incident-tags': IncidentTagsComponent,
    'incident-assignees': IncidentAssigneesComponent,
    'changelog-loggable': ChangelogLoggable
  },
  data () {
    return {
      search: '',
      refreshing: false,
      showDialog: false,
      addCommentDialog: false,
      settingsDialog: false,
      pagination: {
        rowsPerPage: 25
      },
      filter: 'open',
      selectedTags: [],
      dataTags: this.tags,
      creator: null,
      assignee: null,
      assignees: this.managerUsers
    }
  },
  props: {
    incidents: {
      type: Array,
      default: function () {
        return undefined
      }
    },
    incident: {
      type: Object,
      default: function () {
        return undefined
      }
    },
    incidentUsers: {
      type: Array,
      default: function () {
        return []
      }
    },
    managerUsers: {
      type: Array,
      default: function () {
        return []
      }
    },
    tags: {
      type: Array,
      default: function () {
        return []
      }
    }
  },
  computed: {
    filteredAssignees () {
      if (window.user && window.user.id) return this.moveLoggedUserToFirstPosition(this.assignees)
      return this.assignees
    },
    creators () {
      let creators = this.dataIncidents ? this.dataIncidents.map(incident => {
        return {
          id: incident.user_id,
          name: incident.user_name,
          email: incident.user_email,
          hashid: incident.user_hashid,
          full_search: incident.user_id + ' ' + incident.user_name + ' ' + incident.user_email
        }
      }) : []
      if (window.user && window.user.id) {
        return this.moveLoggedUserToFirstPosition(creators)
      }
      return creators
    },
    dataIncidents () {
      return this.$store.getters.incidents
    },
    openIncidents () {
      return this.dataIncidents && this.dataIncidents.filter(incident => incident.closed_at === null)
    },
    closedIncidents () {
      return this.dataIncidents && this.dataIncidents.filter(incident => incident.closed_at !== null)
    },
    filteredIncidents: function () {
      let filteredByState = filters[this.filter](this.dataIncidents)
      if (this.creator) filteredByState = filteredByState.filter(incident => { return incident.user_id === this.creator })
      if (this.assignee) {
        filteredByState = filteredByState.filter(incident => {
          return incident.assignees.map(assignee => assignee['id']).includes(this.assignee)
        })
      }
      if (this.selectedTags.length > 0) {
        filteredByState = filteredByState.filter(incident => {
          return incident.tags.some(tag => this.selectedTags.includes(tag.id))
        })
      }
      return filteredByState
    },
    headers () {
      let headers = []
      headers.push({ text: 'Id', align: 'left', value: 'id', width: '1%' })
      // if (this.showJobTypeHeader) {
      //   headers.push({text: 'Tipus', value: 'type'})
      // }
      headers.push({ text: 'Usuari', value: 'user_name' })
      headers.push({ text: 'Títol', value: 'subject' })
      headers.push({ text: 'Description', value: 'description' })
      if (this.filter !== 'open') headers.push({ text: 'Tancada', value: 'closed_at_timestamp' })
      headers.push({ text: 'Etiquetes', value: 'tags' })
      headers.push({ text: 'Assignada', value: 'assignees' })
      headers.push({ text: 'Creada', value: 'created_at_timestamp' })
      headers.push({ text: 'Última modificació', value: 'updated_at_timestamp' })
      // user_email is added as value to allow searching by email!
      headers.push({ text: 'Accions', value: 'user_email', sortable: false })
      return headers
    }
  },
  // TODO ESBORRAR
  // watch: {
  //   incidents: {
  //     handler: function (newIncidents) {
  //       console.log('###############AAAAAAAAAAAAAAAAAAAA')
  //       console.log(newIncidents)
  //     },
  //     deep: true
  //   }
  // },
  methods: {
    moveLoggedUserToFirstPosition (users) {
      let loggedUser = users.find(user => {
        return user.id === window.user.id
      })
      if (loggedUser) {
        users.splice(users.indexOf(loggedUser), 1)
        users.unshift(loggedUser)
      }
      return users
    },
    showClosedIncidents () {
      this.filter = 'closed'
    },
    showOpenIncidents () {
      this.filter = 'open'
    },
    showAll () {
      this.filter = 'all'
    },
    refresh (message = true) {
      this.fetch(message)
    },
    fetch (message = true) {
      this.refreshing = true
      this.$store.dispatch(actions.SET_INCIDENTS).then(response => {
        if (message) this.$snackbar.showMessage('Incidències actualitzades correctament')
        this.refreshing = false
      }).catch(error => {
        this.$snackbar.showError(error)
        this.refreshing = false
      })
    }
  },
  created () {
    if (this.incidents === undefined) this.fetch()
    else this.$store.commit(mutations.SET_INCIDENTS, this.incidents)
    this.filters = Object.keys(filters)
    if (this.incident) {
      this.showDialog = this.incident.id
      this.filter = 'all'
    }
  }
}
</script>

<style scoped>
    .pointer {
        cursor: pointer;
    }
    .bolder {
        font-weight: bold;
    }
    .no-wrap {
        white-space: nowrap;
    }
</style>
