export default [
{ heading: 'Schedule' },
  {
    title: 'Schedule Player',
    icon: { icon: 'tabler-checkbox' },
    children: [
      { title: 'Schedule Match', to: 'dashboards-schedule-match-list', },
      { title: 'Schedule Training', to: 'dashboards-schedule-training-list', },
      // { title: 'Sport', to: 'dashboards-sport-list', },
    ],
  },
]
