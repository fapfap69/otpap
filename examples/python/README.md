# Python Hello World Example

This example uses FastAPI and a small OTPAP helper module.

## Files

- `requirements.txt`
- `main.py`
- `otpap.py`

## Run

```bash
cd examples/python
python3 -m venv .venv
. .venv/bin/activate
pip install -r requirements.txt
uvicorn main:app --reload
```

## Endpoints

- `GET /page` returns a Hello World page payload and a token.
- `POST /api/hello` validates the token and returns `Hello World`.
