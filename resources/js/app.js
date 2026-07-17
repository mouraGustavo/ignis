import Chart from 'chart.js/auto';

// Exposto globalmente para ser instanciado inline nas views de relatórios
// (resources/views/livewire/admin/relatorios.blade.php), sem precisar de
// mais nenhuma dependência de bundling por página.
window.Chart = Chart;
