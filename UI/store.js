// store.js
import { reactive } from 'vue';

// Define your global state
const state = reactive({
  isLoading: false,
  user: null,
  isAuthenticated: false,
  theme: 'light', // Example: light or dark theme
});

// Define actions (optional but useful for organized updates)
const actions = {
  setUser(user) {
    state.user = user;
    state.isAuthenticated = !!user; // Set authentication state
  },
  toggleTheme() {
    state.theme = state.theme === 'light' ? 'dark' : 'light';
  },
  setLoader(spinner){
    state.isLoading = spinner
  }
};

export { state, actions };
