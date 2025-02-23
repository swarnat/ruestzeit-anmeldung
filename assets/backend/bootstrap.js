import { startStimulusApp } from '@symfony/stimulus-bundle';

import ColumnConfigController from "./controllers/column-config_controller.js"

const app = startStimulusApp();


// register any custom, 3rd party controllers here
app.register('column-config', ColumnConfigController);

// bootstrap.js
