# Heroku Git


## Deploy using Heroku Git
Use git in the command line or a GUI tool to deploy this app.

#### Install the Heroku CLI
Download and install the [Heroku CLI](https://devcenter.heroku.com/articles/heroku-command-line).
If you haven't already, log in to your Heroku account and follow the prompts to create a new SSH public key.
```
$ heroku login
```

#### Clone the repository
Use Git to clone neptune-isis-1111's source code to your local machine.
```
$ heroku git:clone -a neptune-isis-1111 
$ cd neptune-isis-1111
```

#### Deploy your changes
Make some changes to the code you just cloned and deploy them to Heroku using Git.
```
$ git add .
$ git commit -am "make it better"
$ git push heroku main
```

---

You can now change your main deploy branch from "master" to "main" for both manual and automatic deploys, please 
follow the instructions [here](https://help.heroku.com/O0EXQZTA/how-do-i-switch-branches-from-master-to-main).
