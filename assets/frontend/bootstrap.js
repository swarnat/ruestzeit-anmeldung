import { startStimulusApp } from '@symfony/stimulus-bundle';

import ruestzeit_controller from './controllers/ruestzeit_controller.js';

const app = startStimulusApp();

app.register('ruestzeit', ruestzeit_controller);
// bootstrap.js
