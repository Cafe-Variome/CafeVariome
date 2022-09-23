## Cafe Variome
---

This is the repository for Cafe Variome in CodeIgniter 4. 

For an overview of Cafe Variome, please go to https://www.cafevariome.org/

For documentation, please visit https://cafe-variome.gitbook.io/cafe-variome-docs/

## Getting started
#### Detailed instructions available [here](https://cafe-variome.gitbook.io/cafe-variome-docs/how-to-install-it/).
---  
### Checklist: 
- [ ] Satisfy [system requirements]()
- [ ] Create database
- [ ] Configure .env
- [ ] Install dependencies
- [ ] Install Cafe Variome

### Cloning the repository:

$ `git clone https://github.com/Cafe-Variome/CafeVariome.git`  

### Changing Ownership and renaming directories:

$ `mv CafeVariome/ your_webserver_directory/`  
$ `sudo chown $USER:$USER your_webserver_directory -R`

### Editing configuration in env template:

Cafe Variome comes packaged with an [env](env) template. All you have to do is add server URL and database details in the relevant fields of the template as [shown here](https://cafe-variome.gitbook.io/cafe-variome-docs/how-to-install-it/installing-cafe-variome#configuring-env-template).

### Installing dependencies through Composer:  

In the root directory of Cafe Variome where the composer.json resides, run the below command:

$ `composer install`

### Importing Cafe Variome Database and setting permissions through Composer

In the root directory of Cafe Variome where composer.json resides, run the following command:

$ `composer CVInstall`

At this step you will be prompted to enter your installation key which has been given to you prior to installing the software.
Also, you will need to enter the URL to the Cafe Variome Net server you wish to use. Please note that you need to point Cafe Variome to that Cafe Variome Net instance which has issued your installation key.

### Further Steps
Configuring the software and the process of making your data discoverable is described in detail [here](https://cafe-variome.gitbook.io/cafe-variome-docs/how-to-install-it/quick-start). 
