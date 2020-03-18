#!/bin/bash
source .venv/bin/activate
exec gunicorn sdbs_pile.wsgi -b 127.0.0.1:8093