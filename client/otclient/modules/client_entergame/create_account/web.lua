CreateAccountWeb = {}

local lastRequestTime = {}
local REQUEST_COOLDOWN = 500

function CreateAccountWeb.reportRequestWarning(requestType, msg, errorCode)
    g_logger.warning(("[Webscraping - %s] %s"):format(requestType, msg), errorCode)
end

function CreateAccountWeb.handleHttpResponse(requestType, callback, contextValue)
    return function(message, err)
        if err or not message or not message:match("{.*}") then
            if requestType == "getaccountcreationstatus" then
                return callback({
                    RecommendedWorld = "Canary",
                    IsCaptchaDeactivated = true,
                    Worlds = {
                        {
                            Name = "Canary",
                            Region = "South America",
                            PvPType = "Open PvP",
                            PlayersOnline = 0
                        }
                    }
                })
            elseif requestType == "generatecharactername" then
                local names = {"Seragon Xedanna", "Kaelen Vond", "Thalor Drake", "Aron Shadow", "Valerius Dawn"}
                return callback({ CharacterName = names[math.random(#names)] })
            elseif requestType == "checkcharactername" then
                return callback({ CharacterName = "", IsAvailable = true })
            elseif requestType == "checkemail" then
                return callback({ IsValid = true })
            elseif requestType == "checkpassword" then
                local pass = contextValue or ""
                local hasUpper = pass:match("[A-Z]") ~= nil
                local hasLower = pass:match("[a-z]") ~= nil
                local hasNum = pass:match("[0-9]") ~= nil
                local validLen = #pass >= 10 and #pass <= 29
                local invalidChars = false
                local isValid = hasUpper and hasLower and hasNum and validLen

                return callback({
                    PasswordValid = isValid,
                    PasswordStrength = isValid and 3 or 1,
                    PasswordStrengthColor = isValid and "#76EE00" or "#EC644B",
                    PasswordRequirements = {
                        HasUpperCase = hasUpper,
                        HasNumber = hasNum,
                        PasswordLength = validLen,
                        InvalidCharacters = invalidChars,
                        HasLowerCase = hasLower
                    }
                })
            elseif requestType == "createaccountandcharacter" then
                g_platform.openUrl(Services.createAccount or "http://209.126.81.68:8080/index.php/account/create")
                return callback({ success = true })
            end
            CreateAccountWeb.reportRequestWarning(requestType, requestType, "fx handleHttpResponse")
            return callback(nil, err)
        end
        local json_part = message:match("{.*}")
        local status, response = pcall(json.decode, json_part)
        if not status or type(response) ~= "table" then
            if requestType == "getaccountcreationstatus" then
                return callback({
                    RecommendedWorld = "Canary",
                    IsCaptchaDeactivated = true,
                    Worlds = {
                        {
                            Name = "Canary",
                            Region = "South America",
                            PvPType = "Open PvP",
                            PlayersOnline = 0
                        }
                    }
                })
            end
            return callback(nil, "invalid response")
        end
        return callback(response)
    end
end

function CreateAccountWeb.getAccountCreationStatus(callback)
    HTTP.post(Services.createAccount, json.encode({
        type = "getaccountcreationstatus"
    }), CreateAccountWeb.handleHttpResponse("getaccountcreationstatus", callback), false)
end

function CreateAccountWeb.generateCharacterName(callback)
    HTTP.post(Services.createAccount, json.encode({
        type = "generatecharactername"
    }), CreateAccountWeb.handleHttpResponse("generatecharactername", callback), false)
end

function CreateAccountWeb.checkCharacterName(name, callback)
    HTTP.post(Services.createAccount, json.encode({
        type = "checkcharactername",
        CharacterName = name
    }), CreateAccountWeb.handleHttpResponse("checkcharactername", callback), false)
end

function CreateAccountWeb.checkEmail(email, callback)
    HTTP.post(Services.createAccount, json.encode({
        type = "checkemail",
        Email = email
    }), CreateAccountWeb.handleHttpResponse("checkemail", callback), false)
end

function CreateAccountWeb.checkPassword(password, callback)
    HTTP.post(Services.createAccount, json.encode({
        type = "checkpassword",
        Password1 = password
    }), CreateAccountWeb.handleHttpResponse("checkpassword", callback, password), false)
end

function CreateAccountWeb.createAccountAndCharacter(array, callback)
    HTTP.post(Services.createAccount, json.encode({
        type = "createaccountandcharacter",
        EMail = array.email,
        Password = array.password,
        CharacterName = array.characterName,
        CharacterSex = array.characterSex
    }), CreateAccountWeb.handleHttpResponse("createaccountandcharacter", callback), false)
end
