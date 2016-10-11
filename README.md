# http://igeni.us

This is a fork of the [Angular2 full project boilerplate on steroids](https://github.com/voxmachina/angular2-boilerplate)!

You can read all about this project story on [Medium](https://medium.com/dinomad/angular2-boilerplate-on-steroids-86dcf7390542#.8ekof7gjh)

## Dependencies

- [Node v6+](https://nodejs.org/en/)
- [Docker](https://www.docker.com/)
- [Composer](https://getcomposer.org/)
- [Gulp](http://gulpjs.com/)



## Installation

- `npm install`
- navigate to: api/www/services/content and do `composer install`



## Configuration

- gulp.json
  - `cp config/gulp.example.json config/gulp.json`
  - Fill in your details:
    - ssh for later deploy usage to your server
    - googleKey for analytics site ID ([the first part of the html file](https://support.google.com/webmasters/answer/35179?hl=en))
    - cloudfare credentials ([signup now for the free plan](https://www.cloudflare.com/plans/))
- .env
  - `cp api/www/services/content/.env.example api/www/services/content/.env`
  - Your [Instagram API credentials](https://bobmckay.com/web/simple-tutorial-for-getting-an-instagram-clientid-and-access-token/) if you plan to use it

## Servers

- `npm start` , starts the front-end server, be sure to have a build first with `gulp build`
- Configure your back-end server:
  - Navigate to api folder and do: `docker build -t ngboilerplate .` to build the image, you only need to do this once, be sure to configure the start script also included in the api folder and [start your docker service](https://docs.docker.com/engine/admin/).
  - Configure your shared folder path both in api/start script
  - `sh api/start` , starts the back-end server



## Tasks

- `gulp build`
  - Task to perform to do a simple build and test the site with `npm start` afterwards
- `gulp dev`
  - Task to be used while developing
- `gulp stage`
  - Task to test your build before deploying
- `gulp deploy`
  - Task to deploy a new release to your server, creating a new symlink and keeping older versions for a rollback if needed



## License

MIT

