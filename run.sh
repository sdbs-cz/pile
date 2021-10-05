#!/bin/bash
exec poetry run gunicorn sdbs_pile.wsgi -b 127.0.0.1:8093
