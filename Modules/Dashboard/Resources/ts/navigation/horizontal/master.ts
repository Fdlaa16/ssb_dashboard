export default [
  {
    title: 'Master',
    icon: { icon: 'tabler-layout-grid-add' },
    children: [
      {
        title: 'Master Data',
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
          {
            title: 'Stadium',
            to: 'dashboards-stadium-list',
          },
          {
            title: 'Slide Home',
            to: 'dashboards-slide-home-list',
          },
        ],
      },
    ],
  },
]
