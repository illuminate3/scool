<template>
    <form>
        <v-container fluid grid-list-md text-xs-center>
            <v-layout row wrap>
                <v-progress-linear v-if="loading" :indeterminate="true"></v-progress-linear>
                <v-flex md12 v-for="(setting, key)  in settings" :key="setting.key">
                    <v-text-field
                            v-model="settings[key].value"
                            :label="setting.label"
                            :hint="setting.hint"
                    ></v-text-field>
                </v-flex>
            </v-layout>
        </v-container>
        <v-btn color="primary" @click.native="save" class="hidden-sm-and-down" :loading="saving" :disabled="saving">
            <v-icon right dark class="mr-2">save</v-icon> Guardar
        </v-btn>
    </form>
</template>

<script>
export default {
  name: 'Settings',
  data () {
    return {
      loading: false,
      saving: false,
      settings: []
    }
  },
  props: {
    module: {
      type: String,
      required: true
    },
    title: {
      type: String,
      required: true
    }
  },
  methods: {
    save () {
      this.saving = true
      window.axios.put('/api/v1/settings/filter/incidents', { settings: this.settings }).then(response => {
        this.saving = false
        this.$snackbar.showMessage('Configuració modificada correctament')
        this.$emit('close')
      }).catch(error => {
        this.$snackbar.showError(error)
        this.saving = false
      })
    }
  },
  created () {
    this.loading = true
    window.axios.get('/api/v1/settings/filter/incidents').then(response => {
      this.loading = false
      this.settings = response.data
    }).catch(error => {
      this.$snackbar.showError(error)
      this.loading = false
    })
  }
}
</script>
