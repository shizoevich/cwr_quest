<template>
  <div class="container" v-loading.fullscreen.lock="loading">
    <div class="row">
      <div class="vue-wrapper">
        <h2 class="text-center">Synchronization with OfficeAlly</h2>
        <div class="panel panel-default">
          <div class="panel-body">
            <h4>Please choose items for synchronize with OfficeAlly</h4>
            <div v-for="parser in parsers" v-if="parser.allow_manual_start">
              <el-checkbox v-model="parser.checked" :disabled="parser.status != 0">{{ parser.title }}</el-checkbox>
              <span style="color:#67C23A" v-if="parser.status == 1">(Running)</span>
              <span style="color:#409EFF" v-else-if="parser.status == 0 && parser.started_at" >(Synchronized At {{ $moment(parser.started_at).format('MM/DD/YYYY hh:mm A') }})</span>
              <Help v-if="parser.description" :content="parser.description"></Help>
            </div>
            <br>
            <el-button type="primary" @click="sync" :disabled="!checked_parser_ids.length">Sync</el-button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import Help from '../Help';
export default {
  components: {Help},

  data() {
    return {
      parsers: [],
      loading: false,
    };
  },

  mounted() {
    this.initParsers();
    window.Echo.private('parsers')
      .listen('.status.changed', () => {
        this.initParsers(false);
      });
  },

  computed: {
    checked_parsers() {
      return this.parsers.filter(parser => parser.checked);
    },

    checked_parser_ids() {
      return this.checked_parsers.map(parser => parser.id);
    }
  },

  methods: {
    initParsers(withLoader = true) {
      if(withLoader) {
        this.loading = true;
      }
      axios.get('/api/system/parsers').then(response => {
        this.parsers = response.data.parsers.map(parser => {
          parser.checked = false;

          return parser;
        });
      }).finally(() =>  this.loading = false);
    },
    sync() {
      this.loading = true;
      axios.post('/api/system/parsers/run', {ids: this.checked_parser_ids}).finally(() => this.initParsers());
    },
  },
}
</script>
