import * as ActionTypes from './actionTypes';

function settingsReducer(state, action) {
  switch (action.type) {
    case ActionTypes.SET_SETTINGS:
      return {
        ...state,
        ...action.payload,
      };

    case ActionTypes.UPDATE_GENERAL_SETTING:
      return {
        ...state,
        [action.payload.key]: action.payload.value,
      };

    case ActionTypes.UPDATE_COOKIE_SETTING:
      return {
        ...state,
        cookie: {
          ...state.cookie,
          [action.payload.key]: action.payload.value,
        },
      };

    case ActionTypes.UPDATE_CONSENT_MODAL_SETTING:
      return {
        ...state,
        guiOptions: {
          ...state.guiOptions,
          consentModal: {
            ...state.guiOptions.consentModal,
            [action.payload.key]: action.payload.value,
          },
        },
      };

    case ActionTypes.UPDATE_PREFERENCES_MODAL_SETTING:
      return {
        ...state,
        guiOptions: {
          ...state.guiOptions,
          preferencesModal: {
            ...state.guiOptions.preferencesModal,
            [action.payload.key]: action.payload.value,
          },
        },
      };

    case ActionTypes.ADD_LANGUAGE:
      return {
        ...state,
        language: {
          ...state.language,
          translations: {
            ...state.language.translations,
            [action.payload.language]: {
              consentModal: {
                title: '',
                description: '',
                acceptAllBtn: '',
                acceptNecessaryBtn: '',
                showPreferencesBtn: '',
                footer: '',
                footerLinks: [
                  { url: '', label: '' },
                  { url: '', label: '' },
                ],
              },
              preferencesModal: {
                title: '',
                savePreferencesBtn: '',
                acceptAllBtn: '',
                acceptNecessaryBtn: '',
                sections: [
                  {
                    title: '',
                    description: '',
                  },
                  {
                    title: '',
                    description: '',
                    linkedCategory: 'necessary',
                  },
                  {
                    title: '',
                    description: '',
                    linkedCategory: 'analytics',
                  },
                  {
                    title: '',
                    description: '',
                    linkedCategory: 'targeting',
                  },
                  {
                    title: '',
                    description: '',
                    linkedCategory: 'preferences',
                  },
                  {
                    title: '',
                    description: '',
                  },
                ],
              },
            },
          },
        },
        pressidiumOptions: {
          ...state.pressidiumOptions,
          cookieTableHeaders: {
            ...state.pressidiumOptions.cookieTableHeaders,
            translations: {
              ...state.pressidiumOptions.cookieTableHeaders.translations,
              [action.payload.language]: {
                name: '',
                domain: '',
                expiration: '',
                path: '',
                description: '',
              },
            },
          },
        },
      };

    case ActionTypes.DELETE_LANGUAGE:
      return {
        ...state,
        language: {
          ...state.language,
          translations: Object.keys(state.language.translations)
            .reduce((acc, key) => {
              if (key !== action.payload.language) {
                acc[key] = state.language.translations[key];
              }
              return acc;
            }, {}),
        },
        pressidiumOptions: {
          ...state.pressidiumOptions,
          cookieTableHeaders: {
            ...state.pressidiumOptions.cookieTableHeaders,
            translations: Object.keys(state.pressidiumOptions.cookieTableHeaders.translations)
              .reduce((acc, key) => {
                if (key !== action.payload.language) {
                  acc[key] = state.pressidiumOptions.cookieTableHeaders.translations[key];
                }
                return acc;
              }, {}),
          },
        },
      };

    case ActionTypes.UPDATE_CONSENT_MODAL_LANGUAGE_SETTING:
      return {
        ...state,
        language: {
          ...state.language,
          translations: {
            ...state.language.translations,
            [action.payload.language]: {
              ...state.language.translations[action.payload.language],
              consentModal: {
                ...state.language.translations[action.payload.language].consentModal,
                [action.payload.key]: action.payload.value,
              },
            },
          },
        },
      };

    case ActionTypes.UPDATE_PREFERENCES_MODAL_LANGUAGE_SETTING:
      return {
        ...state,
        language: {
          ...state.language,
          translations: {
            ...state.language.translations,
            [action.payload.language]: {
              ...state.language.translations[action.payload.language],
              preferencesModal: {
                ...state.language.translations[action.payload.language].preferencesModal,
                [action.payload.key]: action.payload.value,
              },
            },
          },
        },
      };

    case ActionTypes.UPDATE_PREFERENCES_MODAL_BLOCK_LANGUAGE_SETTING:
      return {
        ...state,
        language: {
          ...state.language,
          translations: {
            ...state.language.translations,
            [action.payload.language]: {
              ...state.language.translations[action.payload.language],
              preferencesModal: {
                ...state.language.translations[action.payload.language].preferencesModal,
                sections: [
                  ...state.language.translations[action.payload.language]
                    .preferencesModal.sections.slice(0, action.payload.index),
                  {
                    ...state.language.translations[action.payload.language]
                      .preferencesModal.sections[action.payload.index],
                    [action.payload.key]: action.payload.value,
                  },
                  ...state.language.translations[action.payload.language]
                    .preferencesModal.sections.slice(action.payload.index + 1),
                ],
              },
            },
          },
        },
      };

    case ActionTypes.ADD_COOKIE_TABLE_ROW:
      return {
        ...state,
        pressidiumOptions: {
          ...state.pressidiumOptions,
          cookieTable: {
            ...state.pressidiumOptions.cookieTable,
            [action.payload.category]: [
              ...state.pressidiumOptions.cookieTable[action.payload.category],
              {
                name: '',
                domain: '',
                expiration: '',
                path: '',
                description: '',
                isRegex: false,
              },
            ],
          },
        },
      };

    case ActionTypes.UPDATE_COOKIE_TABLE_ROW:
      return {
        ...state,
        pressidiumOptions: {
          ...state.pressidiumOptions,
          cookieTable: {
            ...state.pressidiumOptions.cookieTable,
            [action.payload.category]: [
              ...state.pressidiumOptions
                .cookieTable[action.payload.category].slice(0, action.payload.index),
              {
                ...state.pressidiumOptions
                  .cookieTable[action.payload.category][action.payload.index],
                [action.payload.key]: action.payload.value,
              },
              ...state.pressidiumOptions
                .cookieTable[action.payload.category].slice(action.payload.index + 1),
            ],
          },
        },
      };

    case ActionTypes.DELETE_COOKIE_TABLE_ROW:
      return {
        ...state,
        pressidiumOptions: {
          ...state.pressidiumOptions,
          cookieTable: {
            ...state.pressidiumOptions.cookieTable,
            [action.payload.category]: [
              ...state.pressidiumOptions
                .cookieTable[action.payload.category].slice(0, action.payload.index),
              ...state.pressidiumOptions
                .cookieTable[action.payload.category].slice(action.payload.index + 1),
            ],
          },
        },
      };

    case ActionTypes.ADD_BLOCKED_SCRIPT:
      return {
        ...state,
        pressidiumOptions: {
          ...state.pressidiumOptions,
          blockedScripts: [
            ...state.pressidiumOptions.blockedScripts,
            {
              src: '',
              category: 'analytics',
              isRegex: false,
            },
          ],
        },
      };

    case ActionTypes.UPDATE_BLOCKED_SCRIPT:
      return {
        ...state,
        pressidiumOptions: {
          ...state.pressidiumOptions,
          blockedScripts: [
            ...state.pressidiumOptions.blockedScripts.slice(0, action.payload.index),
            {
              ...state.pressidiumOptions.blockedScripts[action.payload.index],
              [action.payload.key]: action.payload.value,
            },
            ...state.pressidiumOptions.blockedScripts.slice(action.payload.index + 1),
          ],
        },
      };

    case ActionTypes.DELETE_BLOCKED_SCRIPT:
      return {
        ...state,
        pressidiumOptions: {
          ...state.pressidiumOptions,
          blockedScripts: [
            ...state.pressidiumOptions.blockedScripts.slice(0, action.payload.index),
            ...state.pressidiumOptions.blockedScripts.slice(action.payload.index + 1),
          ],
        },
      };

    case ActionTypes.UPDATE_LANGUAGE_AUTO_DETECT_SETTING:
      return {
        ...state,
        language: {
          ...state.language,
          autoDetect: action.payload.strategy,
        },
      };

    case ActionTypes.UPDATE_PRIMARY_BUTTON_TEXT:
      return {
        ...state,
        language: {
          ...state.language,
          translations: {
            ...state.language.translations,
            [action.payload.language]: {
              ...state.language.translations[action.payload.language],
              consentModal: {
                ...state.language.translations[action.payload.language].consentModal,
                acceptAllBtn: action.payload.value,
              },
            },
          },
        },
      };

    case ActionTypes.UPDATE_SECONDARY_BUTTON_TEXT:
      return {
        ...state,
        language: {
          ...state.language,
          translations: {
            ...state.language.translations,
            [action.payload.language]: {
              ...state.language.translations[action.payload.language],
              consentModal: {
                ...state.language.translations[action.payload.language].consentModal,
                acceptNecessaryBtn: action.payload.value,
              },
            },
          },
        },
      };

    case ActionTypes.UPDATE_COLOR_SETTINGS:
      return {
        ...state,
        pressidiumOptions: {
          ...state.pressidiumOptions,
          colors: {
            ...state.pressidiumOptions.colors,
            ...action.payload,
          },
        },
      };

    case ActionTypes.UPDATE_COLOR_SETTING:
      return {
        ...state,
        pressidiumOptions: {
          ...state.pressidiumOptions,
          colors: {
            ...state.pressidiumOptions.colors,
            [action.payload.key]: action.payload.value,
          },
        },
      };

    case ActionTypes.UPDATE_GCM_SETTINGS:
      return {
        ...state,
        pressidiumOptions: {
          ...state.pressidiumOptions,
          gcm: {
            ...state.pressidiumOptions.gcm,
            ...action.payload,
          },
        },
      };

    case ActionTypes.UPDATE_GCM_SETTING:
      return {
        ...state,
        pressidiumOptions: {
          ...state.pressidiumOptions,
          gcm: {
            ...state.pressidiumOptions.gcm,
            [action.payload.key]: action.payload.value,
          },
        },
      };

    case ActionTypes.ADD_GCM_REGION:
      return {
        ...state,
        pressidiumOptions: {
          ...state.pressidiumOptions,
          gcm: {
            ...state.pressidiumOptions.gcm,
            regions: [
              ...state.pressidiumOptions.gcm.regions,
              {
                country: action.payload.country,
                subdivisions: action.payload.subdivisions,
                default_consent_states: {
                  ad_storage: false,
                  ad_user_data: false, // GCM v2
                  ad_personalization: false, // GCM v2
                  analytics_storage: false,
                  functionality_storage: false,
                  personalization_storage: false,
                  security_storage: false,
                },
              },
            ],
          },
        },
      };

    case ActionTypes.UPDATE_GCM_REGION_SETTING:
      return {
        ...state,
        pressidiumOptions: {
          ...state.pressidiumOptions,
          gcm: {
            ...state.pressidiumOptions.gcm,
            regions: [
              ...state.pressidiumOptions.gcm.regions.slice(0, action.payload.index),
              {
                ...state.pressidiumOptions.gcm.regions[action.payload.index],
                default_consent_states: {
                  ...state.pressidiumOptions.gcm
                    .regions[action.payload.index].default_consent_states,
                  [action.payload.key]: action.payload.value,
                },
              },
              ...state.pressidiumOptions.gcm.regions.slice(action.payload.index + 1),
            ],
          },
        },
      };

    case ActionTypes.DELETE_GCM_REGION: {
      return {
        ...state,
        pressidiumOptions: {
          ...state.pressidiumOptions,
          gcm: {
            ...state.pressidiumOptions.gcm,
            regions: [
              ...state.pressidiumOptions.gcm.regions.slice(0, action.payload.index),
              ...state.pressidiumOptions.gcm.regions.slice(action.payload.index + 1),
            ],
          },
        },
      };
    }

    case ActionTypes.UPDATE_TAG_GATEWAY_SETTINGS:
      return {
        ...state,
        pressidiumOptions: {
          ...state.pressidiumOptions,
          googleTagGateway: {
            ...state.pressidiumOptions.googleTagGateway,
            ...action.payload,
          },
        },
      };

    case ActionTypes.UPDATE_TAG_GATEWAY_SETTING:
      return {
        ...state,
        pressidiumOptions: {
          ...state.pressidiumOptions,
          googleTagGateway: {
            ...state.pressidiumOptions.googleTagGateway,
            [action.payload.key]: action.payload.value,
          },
        },
      };

    case ActionTypes.UPDATE_PRESSIDIUM_OPTION:
      return {
        ...state,
        pressidiumOptions: {
          ...state.pressidiumOptions,
          [action.payload.key]: action.payload.value,
        },
      };

    case ActionTypes.UPDATE_COOKIE_TABLE_HEADERS_LANGUAGE_SETTING:
      return {
        ...state,
        pressidiumOptions: {
          ...state.pressidiumOptions,
          cookieTableHeaders: {
            ...state.pressidiumOptions.cookieTableHeaders,
            translations: {
              ...state.pressidiumOptions.cookieTableHeaders.translations,
              [action.payload.language]: {
                ...state.pressidiumOptions.cookieTableHeaders
                  .translations[action.payload.language],
                [action.payload.key]: action.payload.value,
              },
            },
          },
        },
      };

    case ActionTypes.UPDATE_FONT_SETTING:
      return {
        ...state,
        pressidiumOptions: {
          ...state.pressidiumOptions,
          font: action.payload,
        },
      };

    case ActionTypes.UPDATE_FLOATING_BUTTON_SETTING:
      return {
        ...state,
        pressidiumOptions: {
          ...state.pressidiumOptions,
          floatingButton: {
            ...state.pressidiumOptions.floatingButton,
            [action.payload.key]: action.payload.value,
          },
        },
      };

    case ActionTypes.UPDATE_ENTIRE_LANGUAGE:
      return {
        ...state,
        language: {
          ...state.language,
          [action.payload.language]: {
            ...state.language[action.payload.language],
            ...action.payload.translation,
          },
        },
      };

    case ActionTypes.UPDATE_CLOSE_ICON_SETTING:
      return {
        ...state,
        pressidiumOptions: {
          ...state.pressidiumOptions,
          consentModalCloseIcon: action.payload.value,
        },
      };

    default:
      return state;
  }
}

export default settingsReducer;
