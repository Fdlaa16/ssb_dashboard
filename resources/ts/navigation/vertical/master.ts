export default [
{ heading: 'Master' },
  {
    title: 'Player & Club',
    icon: { icon: 'tabler-logs' },
    children: [
      { title: 'Player', to: 'dashboards-player-list', },
      { title: 'Club', to: 'dashboards-club-list', },
      { title: 'Media', to: 'dashboards-media-list', },
      { title: 'Stadium', to: 'dashboards-stadium-list', },
    ],
  },
]
