export default [
{ heading: 'Master' },
  {
    title: 'Player & Club',
    icon: { icon: 'tabler-checkbox' },
    children: [
      { title: 'Player', to: 'dashboards-player-list', },
      { title: 'Club', to: 'dashboards-club-list', },
      { title: 'Sport', to: 'dashboards-sport-list', },
    ],
  },
]
