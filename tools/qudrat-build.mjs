import { build } from 'vite';

// Both Vite and the Laravel plugin read only this dedicated, non-live environment.
await build({ envDir: 'storage/app/qudrat-local', mode: 'qudrat-local' });
