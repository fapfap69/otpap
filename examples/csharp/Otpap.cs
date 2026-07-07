// OTPAP helper utilities for the ASP.NET Core Hello World example.
using System.Security.Cryptography;
using System.Text;
using System.Text.Json;

namespace OtpapHelloWorld;

public static class Otpap
{
    /// Returns a deterministic JSON string for token signing.
    public static string CanonicalJson(Dictionary<string, object?> value)
    {
        var ordered = value.OrderBy(pair => pair.Key, StringComparer.Ordinal).ToDictionary(pair => pair.Key, pair => pair.Value);
        return JsonSerializer.Serialize(ordered);
    }

    /// Hashes a request body with SHA-256.
    public static string HashBody(string body)
    {
        return Convert.ToHexString(SHA256.HashData(Encoding.UTF8.GetBytes(body))).ToLowerInvariant();
    }

    /// Signs a token with HMAC-SHA256.
    public static string Sign(Dictionary<string, object?> token, string secret)
    {
        var payload = token.Where(pair => pair.Key != "Signature").ToDictionary(pair => pair.Key, pair => pair.Value);
        using var hmac = new HMACSHA256(Encoding.UTF8.GetBytes(secret));
        return Convert.ToHexString(hmac.ComputeHash(Encoding.UTF8.GetBytes(CanonicalJson(payload)))).ToLowerInvariant();
    }

    /// Creates a signed OTPAP token.
    public static Dictionary<string, object?> CreateToken(Dictionary<string, object?> context, string secret, int ttlSeconds = 60)
    {
        var now = DateTimeOffset.UtcNow.ToUnixTimeSeconds();
        var token = new Dictionary<string, object?>
        {
            ["ProtocolVersion"] = "2.0",
            ["ApplicationId"] = context["applicationId"],
            ["SessionId"] = context["sessionId"],
            ["UserId"] = context["userId"],
            ["PageId"] = context["pageId"],
            ["ApiId"] = context["apiId"],
            ["HttpMethod"] = context["httpMethod"],
            ["Nonce"] = Guid.NewGuid().ToString("N")[..16],
            ["SequenceNumber"] = context["sequenceNumber"],
            ["Timestamp"] = now,
            ["Expiration"] = now + ttlSeconds,
            ["BodyHash"] = HashBody(context["body"]!.ToString()!),
            ["Signature"] = string.Empty
        };
        token["Signature"] = Sign(token, secret);
        return token;
    }

    /// Validates an OTPAP token and returns a protocol response.
    public static Dictionary<string, object?> ValidateToken(Dictionary<string, object?> token, Dictionary<string, object?> context, string secret, HashSet<string> replayStore)
    {
        if (!Equals(token["ApplicationId"], context["applicationId"]))
        {
            return new Dictionary<string, object?> { ["valid"] = false, ["code"] = "OTPAP-1005", ["message"] = "Session binding failed." };
        }

        if (!Equals(token["PageId"], context["pageId"]))
        {
            return new Dictionary<string, object?> { ["valid"] = false, ["code"] = "OTPAP-1006", ["message"] = "Page binding failed." };
        }

        if (!Equals(token["ApiId"], context["apiId"]))
        {
            return new Dictionary<string, object?> { ["valid"] = false, ["code"] = "OTPAP-1007", ["message"] = "API binding failed." };
        }

        if (!Equals(token["HttpMethod"], context["httpMethod"]))
        {
            return new Dictionary<string, object?> { ["valid"] = false, ["code"] = "OTPAP-1008", ["message"] = "Method binding failed." };
        }

        if (Convert.ToInt64(token["Expiration"]!) < DateTimeOffset.UtcNow.ToUnixTimeSeconds())
        {
            return new Dictionary<string, object?> { ["valid"] = false, ["code"] = "OTPAP-1004", ["message"] = "Token expired." };
        }

        if (!Equals(token["BodyHash"], HashBody(context["body"]!.ToString()!)))
        {
            return new Dictionary<string, object?> { ["valid"] = false, ["code"] = "OTPAP-1009", ["message"] = "Body hash mismatch." };
        }

        if (!Equals(token["Signature"], Sign(token, secret)))
        {
            return new Dictionary<string, object?> { ["valid"] = false, ["code"] = "OTPAP-1003", ["message"] = "Signature verification failed." };
        }

        var tokenId = HashBody(CanonicalJson(token.Where(pair => pair.Key != "Signature").ToDictionary(pair => pair.Key, pair => pair.Value)));
        if (!replayStore.Add(tokenId))
        {
            return new Dictionary<string, object?> { ["valid"] = false, ["code"] = "OTPAP-1010", ["message"] = "Replay detected." };
        }

        return new Dictionary<string, object?> { ["valid"] = true, ["code"] = "OTPAP-0000", ["message"] = "Token validated and consumed.", ["tokenId"] = tokenId };
    }
}
