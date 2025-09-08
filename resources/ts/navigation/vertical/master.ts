export default [
{ heading: 'Master' },
  {
    title: 'Master Data',
    icon: { icon: 'tabler-logs' },
    children: [
      { title: 'Pemain', to: 'dashboards-player-list', },
      { title: 'Pengurus', to: 'dashboards-structure-list', },
      { title: 'Klub', to: 'dashboards-club-list', },
      { title: 'Media', to: 'dashboards-media-list', },
      { title: 'Stadium', to: 'dashboards-stadium-list', },
      { title: 'Halaman Slide', to: 'dashboards-slide-home-list', },
    ],
  },
]
