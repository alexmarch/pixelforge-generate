```sh
docker run -p 6379:6379 --name phpredis -d redis
```

# Deploy Wasmer
```sh
curl https://get.wasmer.io -sSfL | sh
&&
wasmer self-update
```