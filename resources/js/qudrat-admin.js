import './bootstrap';
import 'flowbite';
import '../../vendor/power-components/livewire-powergrid/dist/tailwind.css';
import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';

// PowerGrid's bundle expects the Livewire global. Register it before loading the bundle,
// then start one Alpine/Livewire instance for this admin area.
window.Livewire = Livewire;
window.Alpine = Alpine;
import('../../vendor/power-components/livewire-powergrid/dist/powergrid').then(() => Livewire.start());
