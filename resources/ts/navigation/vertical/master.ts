export default [
{ heading: 'Master' },
  {
    title: 'Master Data',
    icon: { icon: 'tabler-logs' },
    children: [
      { title: 'Player', to: 'dashboards-player-list', },
      { title: 'Structure', to: 'dashboards-structure-list', },
      { title: 'Club', to: 'dashboards-club-list', },
      { title: 'Media', to: 'dashboards-media-list', },
      { title: 'Stadium', to: 'dashboards-stadium-list', },
      { title: 'Slide Home', to: 'dashboards-slide-home-list', },
    ],
  },
]
