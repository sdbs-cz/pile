#!/bin/bash
source .venv/bin/activate
gunicorn sdbs_pile.wsgi -b 127.0.0.1:8093
