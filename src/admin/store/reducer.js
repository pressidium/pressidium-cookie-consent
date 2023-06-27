function settingsReducer(state, action) {
  switch (action.type) {
    case 'SET_SETTINGS':
      return {
        ...state,
        ...action.payload,
      };

    case 'UPDATE_GENERAL_SETTING':
      return {
        ...state,
        [action.payload.key]: action.payload.value,
      };

    case 'UPDATE_CONSENT_MODAL_SETTING':
      return {
        ...state,
        gui_options: {
          ...state.gui_options,
          consent_modal: {
            ...state.gui_options.consent_modal,
            [action.payload.key]: action.payload.value,
          },
        },
      };

    case 'UPDATE_SETTINGS_MODAL_SETTING':
      return {
        ...state,
        gui_options: {
          ...state.gui_options,
          settings_modal: {
            ...state.gui_options.settings_modal,
            [action.payload.key]: action.payload.value,
          },
        },
      };

    case 'ADD_LANGUAGE':
      return {
        ...state,
        languages: {
          ...state.languages,
          [action.payload.language]: {
            consent_modal: {
              title: '',
              description: '',
              primary_btn: {
                text: '',
              },
              secondary_btn: {
                text: '',
              },
            },
            settings_modal: {
              title: '',
              save_settings_btn: '',
              accept_all_btn: '',
              reject_all_btn: '',
              close_btn_label: '',
              cookie_table_headers: [
                { name: '' },
                { domain: '' },
                { expiration: '' },
                { path: '' },
                { description: '' },
              ],
              blocks: [
                {
                  title: '',
                  description: '',
                },
                {
                  title: '',
                  description: '',
                  toggle: {
                    value: 'necessary',
                    enabled: true,
                    readonly: true,
                  },
                },
                {
                  title: '',
                  description: '',
                  toggle: {
                    value: 'analytics',
                    enabled: false,
                    readonly: false,
                  },
                  cookie_table: [],
                },
                {
                  title: '',
                  description: '',
                  toggle: {
                    value: 'targeting',
                    enabled: false,
                    readonly: false,
                  },
                  cookie_table: [],
                },
                {
                  title: '',
                  description: '',
                },
              ],
            },
          },
        },
      };

    case 'DELETE_LANGUAGE':
      return {
        ...state,
        languages: Object.keys(state.languages).reduce((acc, key) => {
          if (key !== action.payload.language) {
            acc[key] = state.languages[key];
          }
          return acc;
        }, {}),
      };

    case 'UPDATE_CONSENT_MODAL_LANGUAGE_SETTING':
      return {
        ...state,
        languages: {
          ...state.languages,
          [action.payload.language]: {
            ...state.languages[action.payload.language],
            consent_modal: {
              ...state.languages[action.payload.language].consent_modal,
              [action.payload.key]: action.payload.value,
            },
          },
        },
      };

    case 'UPDATE_SETTINGS_MODAL_LANGUAGE_SETTING':
      return {
        ...state,
        languages: {
          ...state.languages,
          [action.payload.language]: {
            ...state.languages[action.payload.language],
            settings_modal: {
              ...state.languages[action.payload.language].settings_modal,
              [action.payload.key]: action.payload.value,
            },
          },
        },
      };

    case 'UPDATE_COOKIE_TABLE_HEADERS_LANGUAGE_SETTING':
      return {
        ...state,
        languages: {
          ...state.languages,
          [action.payload.language]: {
            ...state.languages[action.payload.language],
            settings_modal: {
              ...state.languages[action.payload.language].settings_modal,
              cookie_table_headers: [
                ...state.languages[action.payload.language].settings_modal.cookie_table_headers.slice(0, action.payload.index),
                {
                  ...state.languages[action.payload.language].settings_modal.cookie_table_headers[action.payload.index],
                  [action.payload.key]: action.payload.value,
                },
                ...state.languages[action.payload.language].settings_modal.cookie_table_headers.slice(action.payload.index + 1),
              ],
            },
          },
        },
      };

    case 'UPDATE_SETTINGS_MODAL_BLOCK_LANGUAGE_SETTING':
      return {
        ...state,
        languages: {
          ...state.languages,
          [action.payload.language]: {
            ...state.languages[action.payload.language],
            settings_modal: {
              ...state.languages[action.payload.language].settings_modal,
              blocks: [
                ...state.languages[action.payload.language].settings_modal.blocks.slice(0, action.payload.index),
                {
                  ...state.languages[action.payload.language].settings_modal.blocks[action.payload.index],
                  [action.payload.key]: action.payload.value,
                },
                ...state.languages[action.payload.language].settings_modal.blocks.slice(action.payload.index + 1),
              ],
            },
          },
        },
      };

    case 'ADD_COOKIE_TABLE_ROW':
      return {
        ...state,
        pressidium_options: {
          ...state.pressidium_options,
          cookie_table: {
            ...state.pressidium_options.cookie_table,
            [action.payload.category]: [
              ...state.pressidium_options.cookie_table[action.payload.category],
              {
                name: '',
                domain: '',
                expiration: '',
                path: '',
                description: '',
                is_regex: false,
              },
            ],
          },
        },
      };

    case 'UPDATE_COOKIE_TABLE_ROW':
      return {
        ...state,
        pressidium_options: {
          ...state.pressidium_options,
          cookie_table: {
            ...state.pressidium_options.cookie_table,
            [action.payload.category]: [
              ...state.pressidium_options.cookie_table[action.payload.category].slice(0, action.payload.index),
              {
                ...state.pressidium_options.cookie_table[action.payload.category][action.payload.index],
                [action.payload.key]: action.payload.value,
              },
              ...state.pressidium_options.cookie_table[action.payload.category].slice(action.payload.index + 1),
            ],
          },
        },
      };

    case 'DELETE_COOKIE_TABLE_ROW':
      return {
        ...state,
        pressidium_options: {
          ...state.pressidium_options,
          cookie_table: {
            ...state.pressidium_options.cookie_table,
            [action.payload.category]: [
              ...state.pressidium_options.cookie_table[action.payload.category].slice(0, action.payload.index),
              ...state.pressidium_options.cookie_table[action.payload.category].slice(action.payload.index + 1),
            ],
          },
        },
      };

    case 'ADD_BLOCKED_SCRIPT':
      return {
        ...state,
        pressidium_options: {
          ...state.pressidium_options,
          blocked_scripts: [
            ...state.pressidium_options.blocked_scripts,
            {
              src: '',
              category: 'analytics',
              is_regex: false,
            },
          ],
        },
      };

    case 'UPDATE_BLOCKED_SCRIPT':
      return {
        ...state,
        pressidium_options: {
          ...state.pressidium_options,
          blocked_scripts: [
            ...state.pressidium_options.blocked_scripts.slice(0, action.payload.index),
            {
              ...state.pressidium_options.blocked_scripts[action.payload.index],
              [action.payload.key]: action.payload.value,
            },
            ...state.pressidium_options.blocked_scripts.slice(action.payload.index + 1),
          ],
        },
      };

    case 'DELETE_BLOCKED_SCRIPT':
      return {
        ...state,
        pressidium_options: {
          ...state.pressidium_options,
          blocked_scripts: [
            ...state.pressidium_options.blocked_scripts.slice(0, action.payload.index),
            ...state.pressidium_options.blocked_scripts.slice(action.payload.index + 1),
          ],
        },
      };

    case 'UPDATE_PRIMARY_BUTTON_TEXT':
      return {
        ...state,
        languages: {
          ...state.languages,
          [action.payload.language]: {
            ...state.languages[action.payload.language],
            consent_modal: {
              ...state.languages[action.payload.language].consent_modal,
              primary_btn: {
                ...state.languages[action.payload.language].consent_modal.primary_btn,
                text: action.payload.value,
              },
            },
          },
        },
      };

    case 'UPDATE_PRIMARY_BUTTON_ROLE':
      return {
        ...state,
        pressidium_options: {
          ...state.pressidium_options,
          primary_btn_role: action.payload.value,
        },
      };

    case 'UPDATE_SECONDARY_BUTTON_TEXT':
      return {
        ...state,
        languages: {
          ...state.languages,
          [action.payload.language]: {
            ...state.languages[action.payload.language],
            consent_modal: {
              ...state.languages[action.payload.language].consent_modal,
              secondary_btn: {
                ...state.languages[action.payload.language].consent_modal.secondary_btn,
                text: action.payload.value,
              },
            },
          },
        },
      };

    case 'UPDATE_SECONDARY_BUTTON_ROLE':
      return {
        ...state,
        pressidium_options: {
          ...state.pressidium_options,
          secondary_btn_role: action.payload.value,
        },
      };

    case 'UPDATE_COLOR_SETTINGS':
      return {
        ...state,
        pressidium_options: {
          ...state.pressidium_options,
          colors: {
            ...state.pressidium_options.colors,
            ...action.payload,
          },
        },
      };

    case 'UPDATE_COLOR_SETTING':
      return {
        ...state,
        pressidium_options: {
          ...state.pressidium_options,
          colors: {
            ...state.pressidium_options.colors,
            [action.payload.key]: action.payload.value,
          },
        },
      };

    default:
      return state;
  }
}

export default settingsReducer;
