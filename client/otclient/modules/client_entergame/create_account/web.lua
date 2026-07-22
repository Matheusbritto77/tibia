CreateAccountWeb = {}

local lastRequestTime = {}
local REQUEST_COOLDOWN = 500

function CreateAccountWeb.reportRequestWarning(requestType, msg, errorCode)
    g_logger.warning(("[Webscraping - %s] %s"):format(requestType, msg), errorCode)
end

function CreateAccountWeb.validatePasswordLocally(pass)
    pass = pass or ""
    local hasUpper = pass:match("[A-Z]") ~= nil
    local hasLower = pass:match("[a-z]") ~= nil
    local hasNum = pass:match("[0-9]") ~= nil
    local validLen = #pass >= 10 and #pass <= 29

    local invalidChars = false
    for i = 1, #pass do
        local b = pass:byte(i)
        if b < 32 or b > 126 then
            invalidChars = true
            break
        end
    end

    local isValid = hasUpper and hasLower and hasNum and validLen and (not invalidChars)

    return {
        PasswordValid = isValid,
        PasswordStrength = isValid and 3 or (#pass >= 6 and 2 or 1),
        PasswordStrengthColor = isValid and "#76EE00" or "#EC644B",
        PasswordRequirements = {
            HasUpperCase = hasUpper,
            HasNumber = hasNum,
            PasswordLength = validLen,
            InvalidCharacters = invalidChars,
            HasLowerCase = hasLower
        }
    }
end

function CreateAccountWeb.validateEmailLocally(email)
    email = email or ""
    local isValid = email:match("^[%w%.%_%-]+@[%w%.%_%-]+%.[%w%-]+$") ~= nil and #email >= 6 and #email <= 255
    return {
        IsValid = isValid,
        errorMessage = isValid and nil or "This email address has an invalid format."
    }
end

function CreateAccountWeb.validateCharacterNameLocally(name)
    name = name or ""
    local validLen = #name >= 3 and #name <= 29
    local validStart = name:match("^[A-Z]") ~= nil
    local validChars = name:match("^[a-zA-Z ]+$") ~= nil
    local isValid = validLen and validStart and validChars
    local msg = nil
    if not validStart then
        msg = "The first letter of a name has to be a capital letter."
    elseif not validChars then
        msg = "A character name contains invalid letters."
    elseif not validLen then
        msg = "A character name must be between 3 and 29 characters."
    end
    return {
        CharacterName = name,
        IsAvailable = isValid,
        errorMessage = msg
    }
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
                return callback(CreateAccountWeb.validateCharacterNameLocally(contextValue))
            elseif requestType == "checkemail" then
                return callback(CreateAccountWeb.validateEmailLocally(contextValue))
            elseif requestType == "checkpassword" then
                return callback(CreateAccountWeb.validatePasswordLocally(contextValue))
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
            elseif requestType == "checkpassword" then
                return callback(CreateAccountWeb.validatePasswordLocally(contextValue))
            elseif requestType == "checkemail" then
                return callback(CreateAccountWeb.validateEmailLocally(contextValue))
            elseif requestType == "checkcharactername" then
                return callback(CreateAccountWeb.validateCharacterNameLocally(contextValue))
            end
            return callback(nil, "invalid response")
        end

        if requestType == "checkpassword" and (not response or not response.PasswordRequirements) then
            response = CreateAccountWeb.validatePasswordLocally(contextValue)
        elseif requestType == "checkemail" and (not response or response.IsValid == nil) then
            response = CreateAccountWeb.validateEmailLocally(contextValue)
        elseif requestType == "checkcharactername" and (not response or response.IsAvailable == nil) then
            response = CreateAccountWeb.validateCharacterNameLocally(contextValue)
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
    }), CreateAccountWeb.handleHttpResponse("checkcharactername", callback, name), false)
end

function CreateAccountWeb.checkEmail(email, callback)
    HTTP.post(Services.createAccount, json.encode({
        type = "checkemail",
        Email = email
    }), CreateAccountWeb.handleHttpResponse("checkemail", callback, email), false)
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
