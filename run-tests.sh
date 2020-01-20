#!/usr/bin/env bash
id="php-tokenizer-tests"
docker build -f Dev.Dockerfile -t $id . &&
  docker run --rm -it $id
