<template>
  <div>
    <div class="container" v-if="loading">loading...</div>

    <div class="container" v-if="!loading">
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
          nuxt-link
          to="/"
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

          <b-button type="submit" size="sm" variant="primary">Submit</b-button>
        </b-form>
      </b-card>

      <b-card class="mt-3" header="Payment History">
        <b-table striped hover :items="transactionsFormatted"></b-table>
      </b-card>
    </div>
  </div>
</template>

<script lang="ts">
import axios from "axios";
import Vue from "vue";

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

      account: null,
      transactions: null,

      loading: true
    };
  },

  computed: {
    accountId() {
      return Number(this.$route.params.id);
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

  mounted() {
    const that = this;

    axios
      .get(`http://localhost:8000/api/accounts/${this.accountId}`)
      .then(function(response) {
        if (!response.data.length) {
          window.location = "/";
        } else {
          that.account = response.data[0];

          if (that.account && that.transactions) {
            that.loading = false;
          }
        }
      });

    axios
      .get(
        `http://localhost:8000/api/accounts/${
          that.$route.params.id
        }/transactions`
      )
      .then(function(response) {
        that["transactions"] = response.data;

        var transactions = [];
        for (let i = 0; i < that.transactions.length; i++) {
          that.transactions[i].amount =
            (that.account.currency === "usd" ? "$" : "€") +
            that.transactions[i].amount;

          if (that.account.id != that.transactions[i].to) {
            that.transactions[i].amount = "-" + that.transactions[i].amount;
          }

          transactions.push(that.transactions[i]);
        }

        that.transactions = transactions;

        if (that.account && that.transactions) {
          that.loading = false;
        }
      });
  },

  methods: {
    appendMe(id) {
      return id === this.accountId ? ' (Me)' : '';
    },

    async onSubmit(evt) {
      evt.preventDefault();
      try {
        const result = await axios.post(
          `http://localhost:8000/api/accounts/${this.accountId}/transactions`,
          this.payment
        );
        this.payment = { from: this.currentId, ...INITIAL_PAYMENT };
        this.transactions.push(result);
        this.show = false;
      } catch (error) {

      }
    }
  }
};
</script>
