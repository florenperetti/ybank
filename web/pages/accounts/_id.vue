<template>
  <div>
    <div class="container" v-if="loading">loading...</div>
    <div class="container" v-else>
      <b-alert :show="errorMessage" variant="warning" dismissible>{{errorMessage}}</b-alert>
      <b-card :header="'Welcome, ' + account.name" class="mt-3">
        <b-card-text>
          <div>
            Account: <code>{{ account.id }}</code>
          </div>
          <div>
            Balance:
            <code
              >{{ account.currency === "usd" ? "$" : "€"
              }}{{ account.balance }}</code
            >
          </div>
        </b-card-text>
        <b-button size="sm" variant="success" @click="show = !show"
          >New payment</b-button
        >

        <b-button
          class="float-right"
          variant="danger"
          size="sm"
          @click="handleLogout"
          >Logout</b-button
        >
      </b-card>

      <b-card class="mt-3" header="New Payment" v-show="show">
        <b-form @submit="onSubmit">
          <b-form-group id="input-group-1" label="To:" label-for="input-1">
            <b-form-input
              id="input-1"
              size="sm"
              v-model.number="payment.to"
              type="number"
              required
              placeholder="Destination ID"
            ></b-form-input>
          </b-form-group>

          <b-form-group id="input-group-2" label="Amount:" label-for="input-2">
            <b-input-group prepend="$" size="sm">
              <b-form-input
                id="input-2"
                v-model.number="payment.amount"
                type="number"
                :max="account.balance"
                :min="1"
                required
                placeholder="Amount"
              ></b-form-input>
            </b-input-group>
          </b-form-group>

          <b-form-group id="input-group-3" label="Details:" label-for="input-3">
            <b-form-input
              id="input-3"
              size="sm"
              v-model="payment.details"
              required
              placeholder="Payment details"
            ></b-form-input>
          </b-form-group>

          <b-button :disabled="loadingAddTransaction" type="submit" size="sm" variant="primary">Submit</b-button>
        </b-form>
      </b-card>

      <b-card class="mt-3" header="Payment History">
        <b-table striped hover :items="transactionsFormatted"></b-table>
      </b-card>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import Vue from "vue";
import { mapMutations } from 'vuex';

const INITIAL_PAYMENT = {
  to: null,
  amount: null,
  details: null
}

export default {
  data() {
    return {
      show: false,
      payment: {
        from: Number(this.$route.params.id),
        ...INITIAL_PAYMENT
      },

      loading: true,

      loadingAddTransaction: false,

      errorMessage: null
    };
  },

  asyncData ({ params, error, $axios, store, redirect }) {
    if (!store.state.auth.id || store.state.auth.id !== Number(params.id)) {
      return redirect('/');
    }
    const promises = [
      $axios.get(`http://localhost:8000/api/accounts/${params.id}`),
      $axios.get(`http://localhost:8000/api/accounts/${params.id}/transactions`)
    ]
    return Promise.all(promises)
      .then(response => {
        const [ accountResponse, transactionsResponse ] = response;
        return {
          account: accountResponse.data[0],
          transactions: transactionsResponse.data
        }
      })
      .catch((e) => {
        error({ statusCode: 404, message: 'Not found' })
      });
  },

  async created () {
    this.transactions = this.transactions.map(this.formatTransactions);
    this.loading = false;
  },

  computed: {
    routeAccountId() {
      return Number(this.$route.params.id);
    },

    userId() {
      return this.$store.state.auth.id;
    },

    transactionsFormatted() {
      if (!this.transactions) {
        return null
      }
      return this.transactions.map(({ id, to, from, account_to, account_from, ...data }) => ({
        id,
        from: account_from.name + this.appendMe(from),
        to: account_to.name + this.appendMe(to),
        ...data
      }));
    }
  },

  methods: {
    ...mapMutations({
      logout: 'auth/logout'
    }),

    appendMe(id) {
      return id === this.userId ? ' (Me)' : '';
    },

    formatTransactions(t) {
      return {
        ...t,
        amount: this.userId === t.from ? `-€${t.amount}` : `€${t.amount}`
      };
    },

    async onSubmit(evt) {
      evt.preventDefault();
      this.errorMessage = null;
      this.loadingAddTransaction = true;
      try {
        const result = await axios.post(
          `http://localhost:8000/api/accounts/${this.routeAccountId}/transactions`,
          this.payment
        );
        this.payment = { from: this.currentId, ...INITIAL_PAYMENT };
        this.transactions.push(this.formatTransactions(result.data));
        this.show = false;
      } catch (error) {
        this.errorMessage = 'Something went wrong. Please check the data sent and try again.';
      } finally {
        this.loadingAddTransaction = false;
      }
    },

    handleLogout() {
      this.logout();
      this.$router.push({
        path: '/'
      });
    }
  }
};
</script>
