# Development Documentation

* [Docker](docker.md)
* [Development](development.md)
* [Database](db.md)
* [API](app.md)

### setup DNS entry local
please add next in /etc/hosts

10.120.5.2 @PROJECT_NAME@-app
10.120.5.3 @PROJECT_NAME@-db

### working with docker

###### Log in PHP container as a normal user
```bash
docker-compose exec --user $(id -u):$(id -g) app bash
```

```fish
docker-compose exec --user (id -u):(id -g) app bash
```

###### Log in PHP container as root user
```bash
docker-compose exec --user 0:0 app bash
```
