<template>
    <v-container fluid grid-list-md text-xs-center>
        <v-layout row wrap>
            <v-flex xs12>
                <v-dialog v-model="settingsDialog" fullscreen hide-overlay transition="dialog-bottom-transition">
                    <v-card>
                        <v-toolbar dark color="primary">
                            <v-btn icon dark @click.native="dialog = false">
                                <v-icon>close</v-icon>
                            </v-btn>
                            <v-toolbar-title>Configuració Usuaris Ldap</v-toolbar-title>
                            <v-spacer></v-spacer>
                            <v-toolbar-items>
                                <v-btn dark flat @click.native="dialog = false">Guardar</v-btn>
                            </v-toolbar-items>
                        </v-toolbar>
                        <v-list three-line subheader>
                            <v-subheader>General</v-subheader>
                            <v-list-tile avatar>
                                <v-list-tile-action>
                                    <v-checkbox v-model="googleWatch"></v-checkbox>
                                </v-list-tile-action>
                                <v-list-tile-content>
                                    <!--// TODO-->
                                    <v-list-tile-title>TODO Observar canvis d'usuaris Ldap (Watch)</v-list-tile-title>
                                    <v-list-tile-sub-title>Activar les notificacions push de Ldap per tal de registrar/observar els canvis d'usuaris.
                                        <a href="https://developers.google.com/admin-sdk/directory/v1/guides/push" target="_blank">Documentació</a></v-list-tile-sub-title>
                                </v-list-tile-content>
                            </v-list-tile>
                        </v-list>
                    </v-card>
                </v-dialog>
                <v-toolbar color="blue darken-3">
                    <v-menu bottom>
                        <v-btn slot="activator" icon dark>
                            <v-icon>more_vert</v-icon>
                        </v-btn>

                        <v-list>
                            <v-list-tile>
                                <v-list-tile-title><a target="_blank" href="https://admin.google.com/u/3/ac/users">Panell administració Ldap</a></v-list-tile-title>
                            </v-list-tile>
                        </v-list>
                    </v-menu>
                    <v-toolbar-title class="white--text title">Ldap users</v-toolbar-title>
                    <v-spacer></v-spacer>
                    <v-btn icon class="white--text" @click="settingsDialog = true">
                        <v-icon>settings</v-icon>
                    </v-btn>
                    <v-btn icon class="white--text" @click="refresh" :loading="refreshing" :disabled="refreshing">
                        <v-icon>refresh</v-icon>
                    </v-btn>
                </v-toolbar>
                <v-card>
                    <v-card-text class="px-0 mb-2">
                        <v-card>
                            <v-card-title>
                                TODO Filter here?
                                <v-spacer></v-spacer>
                                <v-text-field
                                        append-icon="search"
                                        label="Buscar"
                                        single-line
                                        hide-details
                                        v-model="search"
                                ></v-text-field>
                            </v-card-title>
                            <v-data-table
                                    class="px-0 mb-2 hidden-sm-and-down"
                                    :headers="headers"
                                    :items="filteredUsers"
                                    :search="search"
                                    item-key="id"
                                    disable-initial-sort
                                    no-results-text="No s'ha trobat cap registre coincident"
                                    no-data-text="No hi han dades disponibles"
                                    rows-per-page-text="Grups per pàgina"
                            >
                                <template slot="items" slot-scope="{ item: user }">
                                    <tr>
                                        <td class="text-xs-left" v-html="user.dn"></td>
                                        <td class="text-xs-left" v-html="user.cn"></td>
                                        <td class="text-xs-left" v-html="user.uid"></td>
                                        <td class="text-xs-left" v-html="user.uidnumber"></td>
                                        <td class="text-xs-left" v-html="user.userpassword"></td>
                                        <td class="text-xs-left">
                                            <a target="_blank" :href="'https://admin.google.com/u/3/ac/users/' + user.id">{{ user.primaryEmail }}</a>
                                        </td>
                                        <td class="text-xs-left" v-html="user.sn1"></td>
                                        <td class="text-xs-left" v-html="user.sn2"></td>
                                        <td class="text-xs-left" v-html="user.givenname"></td>
                                        <td class="text-xs-left" v-html="user.suspensionReason"></td>
                                        <td class="text-xs-left" v-html="user.lastLoginTime"></td>
                                        <td class="text-xs-left" v-html="user.createtimestamp"></td>
                                        <td class="text-xs-left" v-html="user.creatorsName"></td>
                                        <td class="text-xs-left" v-html="user.modifiersName"></td>
                                        <td class="text-xs-left" v-html="user.modifyTimestamp"></td>
                                        <td class="text-xs-left">
                                            <show-ldap-user-icon :user="user" :users="users"></show-ldap-user-icon>
                                            <v-btn icon class="mx-0" @click="">
                                                <v-icon color="teal">edit</v-icon>
                                            </v-btn>
                                            <confirm-icon
                                                    icon="delete"
                                                    color="pink"
                                                    :working="removing"
                                                    @confirmed="remove(user)"
                                                    tooltip="Eliminar"
                                                    message="Esteu segurs que voleu eliminar l'usuari?"
                                            ></confirm-icon>
                                        </td>
                                    </tr>
                                </template>
                            </v-data-table>
                        </v-card>
                    </v-card-text>
                </v-card>
            </v-flex>
        </v-layout>
    </v-container>
</template>

<script>
import { mapGetters } from 'vuex'
import * as mutations from '../../../store/mutation-types'

import withSnackbar from '../../mixins/withSnackbar'
import axios from 'axios'
import ConfirmIcon from '../../ui/ConfirmIconComponent'
import ShowLdapUserIconComponent from './ShowLdapUserIconComponent'

export default {
  name: 'LdapUsersComponent',
  mixins: [withSnackbar],
  components: {
    'confirm-icon': ConfirmIcon,
    'show-ldap-user-icon': ShowLdapUserIconComponent
  },
  data () {
    return {
      search: '',
      removing: false,
      refreshing: false,
      settingsDialog: false,
      googleWatch: false
    }
  },
  computed: {
    ...mapGetters({
      internalUsers: 'googleUsers'
    }),
    filteredUsers: function () {
      return this.internalUsers
    },
    headers () {
      let headers = []
      headers.push({ text: 'Nom1', value: 'fullName' })
      headers.push({ text: 'Nom2', value: 'fullName' })
      headers.push({ text: 'uid', value: 'uid' })
      headers.push({ text: 'uidnumber', value: 'uidnumber' })
      headers.push({ text: 'userpassword', value: 'userpassword' })
      headers.push({ text: 'Correu electrònic', value: 'primaryEmail' })
      headers.push({ text: 'Path', value: 'orgUnitPath' })
      headers.push({ text: 'Administrador', value: 'isAdmin' })
      headers.push({ text: 'Suspès?', value: 'suspended' })
      headers.push({ text: 'Raó suspensió', value: 'suspensionReason' })
      headers.push({ text: 'Últim login', value: 'lastLoginTime' })
      headers.push({ text: 'Data creació', value: 'createtimestamp' })
      headers.push({ text: 'Usuari creació', value: 'creatorsName' })
      headers.push({ text: 'modifiersName', value: 'modifiersName' })
      headers.push({ text: 'modifyTimestamp', value: 'modifyTimestamp' })
      headers.push({ text: 'Accions', sortable: false })
      return headers
    }
  },
  watch: {
    googleWatch (newValue) {
      console.log('googleWatch changed to : ' + newValue)
      if (newValue) {
        axios.get('/api/v1/gsuite/users/watch').then(response => {
          console.log(response)
        }).catch(error => {
          console.log(error)
        })
      }
    }
  },
  props: {
    users: {
      type: Array,
      required: true
    }
  },
  methods: {
    refresh () {
      this.refreshing = true
      axios.get('/api/v1/gsuite/users').then(response => {
        this.refreshing = false
        this.$store.commit(mutations.SET_GOOGLE_USERS, response.data)
      }).catch(error => {
        this.refreshing = false
        console.log(error)
      })
    },
    remove (user) {
      this.removing = true
      axios.delete('/api/v1/gsuite/users/' + user.id).then(response => {
        this.removing = false
        this.$store.commit(mutations.DELETE_GOOGLE_USER, user)
        this.showMessage('Usuari esborrat correctament')
      }).catch(error => {
        this.removing = false
        this.showError(error)
      })
    }
  },
  created () {
    this.$store.commit(mutations.SET_GOOGLE_USERS, this.users)
  }
}
</script>
