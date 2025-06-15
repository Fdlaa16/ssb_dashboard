export default [
  {
    title: 'Master',
    icon: { icon: 'tabler-layout-grid-add' },
    children: [
      {
        title: 'Player & Club',
        icon: { icon: 'tabler-logs' },
        children: [
          {
            title: 'Player',
            to: 'dashboards-player-list',
          },
          {
            title: 'Club',
            to: 'dashboards-club-list',
          },
          {
            title: 'Media',
            to: 'dashboards-media-list',
          },
        ],
      },
    ],
  },
]
