# Demo user
```
login: test@example.com
pass: password
```


# Docker redis
```sh 
docker run -p 6379:6379 --name phpredis -d redis
```

# Deploy Wasmer
```sh
curl https://get.wasmer.io -sSfL | sh
&&
wasmer self-update
```

# Tunel
```sh
docker run cloudflare/cloudflared:latest tunnel --no-autoupdate run --token eyJhIjoiZDNjMGJlN2JiZDY5YjI0NjYyNmQ0ZjY5ZWQ4Zjk1ZjAiLCJ0IjoiMzIzYTA4NGEtNjliYS00OWMyLWIyZTEtZmU5ZmUzNzZmOWIwIiwicyI6IlpUTmpOV0k1WW1VdFpXWTJZeTAwT0RVNExUbG1abUl0WVRnMk56TmxNRGczWTJJeiJ9
```