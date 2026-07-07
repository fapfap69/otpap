"""
OTPAP helper utilities for the Python Hello World example.
"""

from __future__ import annotations

import hashlib
import hmac
import json
import secrets
import time


def canonical_json(value: object) -> str:
    """Returns a deterministic JSON representation."""
    return json.dumps(value, sort_keys=True, separators=(",", ":"), ensure_ascii=False)


def hash_body(body: str) -> str:
    """Hashes the request body with SHA-256."""
    return hashlib.sha256(body.encode("utf-8")).hexdigest()


def sign_token(token: dict, secret: str) -> str:
    """Signs a token using HMAC-SHA256."""
    payload = {key: value for key, value in token.items() if key != "Signature"}
    return hmac.new(secret.encode("utf-8"), canonical_json(payload).encode("utf-8"), hashlib.sha256).hexdigest()


def create_token(context: dict, secret: str, ttl_seconds: int = 60) -> dict:
    """Creates a signed OTPAP token."""
    timestamp = int(time.time())
    token = {
        "ProtocolVersion": "2.0",
        "ApplicationId": context["applicationId"],
        "SessionId": context["sessionId"],
        "UserId": context["userId"],
        "PageId": context["pageId"],
        "ApiId": context["apiId"],
        "HttpMethod": context["httpMethod"],
        "Nonce": secrets.token_hex(8),
        "SequenceNumber": context["sequenceNumber"],
        "Timestamp": timestamp,
        "Expiration": timestamp + ttl_seconds,
        "BodyHash": hash_body(context["body"]),
        "Signature": "",
    }
    token["Signature"] = sign_token(token, secret)
    return token


def validate_token(token: dict, context: dict, secret: str, replay_store: set[str]) -> dict:
    """Validates a token and records replay state on success."""
    now = int(time.time())
    if token.get("ProtocolVersion") != "2.0":
        return {"valid": False, "code": "OTPAP-1001", "message": "Unsupported token format."}
    if token.get("ApplicationId") != context["applicationId"] or token.get("SessionId") != context["sessionId"] or token.get("UserId") != context["userId"]:
        return {"valid": False, "code": "OTPAP-1005", "message": "Session binding failed."}
    if token.get("PageId") != context["pageId"]:
        return {"valid": False, "code": "OTPAP-1006", "message": "Page binding failed."}
    if token.get("ApiId") != context["apiId"]:
        return {"valid": False, "code": "OTPAP-1007", "message": "API binding failed."}
    if token.get("HttpMethod") != context["httpMethod"].upper():
        return {"valid": False, "code": "OTPAP-1008", "message": "Method binding failed."}
    if token.get("Expiration", 0) < now:
        return {"valid": False, "code": "OTPAP-1004", "message": "Token expired."}
    if token.get("BodyHash") != hash_body(context["body"]):
        return {"valid": False, "code": "OTPAP-1009", "message": "Body hash mismatch."}

    expected_signature = sign_token(token, secret)
    if not hmac.compare_digest(token.get("Signature", ""), expected_signature):
        return {"valid": False, "code": "OTPAP-1003", "message": "Signature verification failed."}

    token_id = hashlib.sha256(canonical_json({k: v for k, v in token.items() if k != "Signature"}).encode("utf-8")).hexdigest()
    if token_id in replay_store:
        return {"valid": False, "code": "OTPAP-1010", "message": "Replay detected."}

    replay_store.add(token_id)
    return {"valid": True, "code": "OTPAP-0000", "message": "Token validated and consumed.", "tokenId": token_id}
