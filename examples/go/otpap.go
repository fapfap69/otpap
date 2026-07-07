// OTPAP helpers for the Go Hello World example.
package main

import (
	"crypto/hmac"
	"crypto/rand"
	"crypto/sha256"
	"encoding/hex"
	"sort"
	"strconv"
	"strings"
)

func canonicalJSON(value any) string {
	var builder strings.Builder
	writeCanonicalJSON(&builder, canonicalize(value))
	return builder.String()
}

func canonicalize(value any) any {
	switch typed := value.(type) {
	case map[string]any:
		keys := make([]string, 0, len(typed))
		for key := range typed {
			keys = append(keys, key)
		}
		sort.Strings(keys)
		ordered := make(map[string]any, len(typed))
		for _, key := range keys {
			ordered[key] = canonicalize(typed[key])
		}
		return ordered
	case []any:
		out := make([]any, len(typed))
		for index, item := range typed {
			out[index] = canonicalize(item)
		}
		return out
	default:
		return value
	}
}

func writeCanonicalJSON(builder *strings.Builder, value any) {
	switch typed := value.(type) {
	case map[string]any:
		builder.WriteByte('{')
		keys := make([]string, 0, len(typed))
		for key := range typed {
			keys = append(keys, key)
		}
		sort.Strings(keys)
		for index, key := range keys {
			if index > 0 {
				builder.WriteByte(',')
			}
			builder.WriteString(strconv.Quote(key))
			builder.WriteByte(':')
			writeCanonicalJSON(builder, typed[key])
		}
		builder.WriteByte('}')
	case []any:
		builder.WriteByte('[')
		for index, item := range typed {
			if index > 0 {
				builder.WriteByte(',')
			}
			writeCanonicalJSON(builder, item)
		}
		builder.WriteByte(']')
	case string:
		builder.WriteString(strconv.Quote(typed))
	case bool:
		if typed {
			builder.WriteString("true")
		} else {
			builder.WriteString("false")
		}
	case int:
		builder.WriteString(strconv.Itoa(typed))
	case int64:
		builder.WriteString(strconv.FormatInt(typed, 10))
	case float64:
		builder.WriteString(strconv.FormatFloat(typed, 'f', -1, 64))
	default:
		builder.WriteString("null")
	}
}

func hashBody(body string) string {
	sum := sha256.Sum256([]byte(body))
	return hex.EncodeToString(sum[:])
}

func signToken(token map[string]any, secret string) string {
	payload := make(map[string]any, len(token))
	for key, value := range token {
		if key != "Signature" {
			payload[key] = value
		}
	}
	mac := hmac.New(sha256.New, []byte(secret))
	mac.Write([]byte(canonicalJSON(payload)))
	return hex.EncodeToString(mac.Sum(nil))
}

func createToken(context map[string]any, secret string, ttlSeconds int64) map[string]any {
	token := map[string]any{
		"ProtocolVersion": "2.0",
		"ApplicationId":   context["applicationId"],
		"SessionId":       context["sessionId"],
		"UserId":          context["userId"],
		"PageId":          context["pageId"],
		"ApiId":           context["apiId"],
		"HttpMethod":      context["httpMethod"],
		"Nonce":           randomHex(8),
		"SequenceNumber":  context["sequenceNumber"],
		"Timestamp":       context["timestamp"],
		"Expiration":      context["timestamp"].(int64) + ttlSeconds,
		"BodyHash":        hashBody(context["body"].(string)),
		"Signature":       "",
	}
	token["Signature"] = signToken(token, secret)
	return token
}

func validateToken(token map[string]any, context map[string]any, secret string, replayStore map[string]bool) map[string]any {
	if token["ApplicationId"] != context["applicationId"] {
		return map[string]any{"valid": false, "code": "OTPAP-1005", "message": "Session binding failed."}
	}
	if token["PageId"] != context["pageId"] {
		return map[string]any{"valid": false, "code": "OTPAP-1006", "message": "Page binding failed."}
	}
	if token["ApiId"] != context["apiId"] {
		return map[string]any{"valid": false, "code": "OTPAP-1007", "message": "API binding failed."}
	}
	if strings.ToUpper(token["HttpMethod"].(string)) != strings.ToUpper(context["httpMethod"].(string)) {
		return map[string]any{"valid": false, "code": "OTPAP-1008", "message": "Method binding failed."}
	}
	if token["Expiration"].(int64) < context["timestamp"].(int64) {
		return map[string]any{"valid": false, "code": "OTPAP-1004", "message": "Token expired."}
	}
	bodyHash := hashBody(context["body"].(string))
	if token["BodyHash"] != bodyHash {
		return map[string]any{"valid": false, "code": "OTPAP-1009", "message": "Body hash mismatch."}
	}
	expectedSignature := signToken(token, secret)
	if !hmac.Equal([]byte(token["Signature"].(string)), []byte(expectedSignature)) {
		return map[string]any{"valid": false, "code": "OTPAP-1003", "message": "Signature verification failed."}
	}
	tokenID := tokenId(token)
	if replayStore[tokenID] {
		return map[string]any{"valid": false, "code": "OTPAP-1010", "message": "Replay detected."}
	}
	replayStore[tokenID] = true
	return map[string]any{"valid": true, "code": "OTPAP-0000", "message": "Token validated and consumed.", "tokenId": tokenID}
}

func tokenId(token map[string]any) string {
	payload := make(map[string]any, len(token))
	for key, value := range token {
		if key != "Signature" {
			payload[key] = value
		}
	}
	sum := sha256.Sum256([]byte(canonicalJSON(payload)))
	return hex.EncodeToString(sum[:])
}

func randomHex(bytes int) string {
	buffer := make([]byte, bytes)
	_, _ = rand.Read(buffer)
	return strings.ToLower(hex.EncodeToString(buffer))
}
