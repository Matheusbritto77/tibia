-- https://github.com/opentibiabr/myaac/pull/33/files

local MainWindowsCreateAccount = nil

local UIwidgetImagen = {
    AccountData = nil,
    AllData = nil,
    CharacterData = nil
}

local UIComboBox = {
    world = nil,
    pvp = nil
}

local UITextEdit = {
    email = nil,
    password = nil,
    repeatPassword = nil,
    character = nil
}

local iconsCreateAccount = {
    Password = nil,
    Email = nil,
    RepeatPassword = nil,
    CheckBox = nil
}
local iconsCreateCharacter = {
    Sex = nil,
    RecommendedWorld = nil,
    CharacterName = nil
}
local UITextList = {
    listAllWorlds = nil
}

local UIlabel = {
    RecommendedWorld = nil,
    passwordSecurityLevel = nil,
    titleMiniPanelWorld = nil
}

local globalInfo = {
    email = "",
    password = "",
    characterName = "",
    characterSex = "",
    selectedWorld = ""
}

local toolstips = {
    password = nil,
    allExceptPassword = nil
}

local auxWidgets = {
    worldDefault = nil
}

local Worlds = {}

local sexModeGroup = nil
g_ui.importStyle('create_account/styles')

dofile('create_account/web')

local getAccountCreationStatus = CreateAccountWeb.getAccountCreationStatus
local generateCharacterName = CreateAccountWeb.generateCharacterName
local checkCharacterName = CreateAccountWeb.checkCharacterName
local checkEmail = CreateAccountWeb.checkEmail
local checkPassword = CreateAccountWeb.checkPassword
local createAccountAndCharacter = CreateAccountWeb.createAccountAndCharacter

-- /*=============================================
-- =            successfull Check               =
-- =============================================*/
local function checkAllRequirements()
    local function allWidgetsEnabled(widgets)
        for _, widget in pairs(widgets) do
            if not widget:isEnabled() then
                return false
            end
        end
        return true
    end
    local createAccountPassed = allWidgetsEnabled(iconsCreateAccount)
    local createYourCharacterPassed = allWidgetsEnabled(iconsCreateCharacter)
    UIwidgetImagen.AccountData:setEnabled(createAccountPassed)
    UIwidgetImagen.AllData:setEnabled(createAccountPassed and createYourCharacterPassed)
    UIwidgetImagen.CharacterData:setEnabled(createYourCharacterPassed)
    return createAccountPassed and createYourCharacterPassed
end

local function updateButtonState(button)
    button:setEnabled(checkAllRequirements())
end

local function setRequirementState(widget, enabled, widgetError, errorMessage)
    widget:setEnabled(enabled)
    updateButtonState(MainWindowsCreateAccount.createAccount.buttonStartPlaying)

    if widgetError then
        local errorWidget = toolstips.allExceptPassword:getChildById(widgetError:getId())
        errorWidget:setVisible(not enabled)
        if errorMessage then
            errorWidget:setText(errorMessage)
        else
            errorWidget:setVisible(false)
        end
    end
end

local function updatePasswordRequirements(response)
    if not response or not response.PasswordRequirements then
        return
    end

    local requirements = response.PasswordRequirements
    local requirementsPanel = toolstips.password

    local allRequirementsPassed = true

    if requirementsPanel then
        for requirement, value in pairs(requirements) do
            local reqPanel = requirementsPanel:getChildById(requirement)
            if reqPanel then
                local icon = reqPanel:getChildById('icons')
                if icon then
                    local passed = value
                    if requirement == "InvalidCharacters" then
                        passed = not value
                    end
                    icon:setEnabled(passed)
                    if not passed then
                        allRequirementsPassed = false
                    end
                end
            end
        end
    end
    setRequirementState(iconsCreateAccount.Password, allRequirementsPassed)
end

local function handleTextChange(widget, requestType, validationFunc)
    local currentTime = g_clock.millis()
    local lastTime = lastRequestTime[widget:getId()] or 0
    local text = widget:getText()
    if #text == 0 then
        return
    end
    local processResponse = function(response, err)
        if err then
            widget:setColor("#EC644B")
            return
        end

        local isValid = (requestType == "email" and not response.errorCode) or
                            (requestType == "character" and response.IsAvailable) or
                            (requestType == "password" and response.PasswordValid)

        widget:setColor(isValid and "#76EE00" or "#EC644B")

        if requestType == "password" then
            updatePasswordRequirements(response)
        elseif requestType == "character" then
            setRequirementState(iconsCreateCharacter.CharacterName, response.IsAvailable, widget, response.errorMessage)
        elseif requestType == "email" then
            setRequirementState(iconsCreateAccount.Email, response.IsValid, widget, response.errorMessage)

        end
    end
    if currentTime - lastTime < REQUEST_COOLDOWN then
        if widget.pendingEvent then
            removeEvent(widget.pendingEvent)
            widget.pendingEvent = nil
        end

        widget.pendingEvent = scheduleEvent(function()
            lastRequestTime[widget:getId()] = g_clock.millis()
            validationFunc(text, processResponse)
        end, REQUEST_COOLDOWN)
    else
        lastRequestTime[widget:getId()] = currentTime
        validationFunc(text, processResponse)
    end
end

-- /*=============================================
-- =                onXXXXChangeEvent            =
-- =============================================*/

local function onFocusChange(focused, reason)
    if #focused:getText() == 0 then
        return
    end
    local focusedId = focused:getId()
    if focusedId == "textEditPassword" then
        toolstips.password:setVisible(reason)
        return
    end

    -- test
    local tooltip = toolstips.allExceptPassword:getChildById(focusedId)
    local shouldHide = false
    if focusedId == "textEditEmail" and iconsCreateAccount.Email:isEnabled() then
        shouldHide = true
    elseif focusedId == "textEditRepeatPassword" and iconsCreateAccount.RepeatPassword:isEnabled() then
        shouldHide = true
    elseif focusedId == "textEditCharacter" and iconsCreateAccount.CheckBox:isEnabled() then
        shouldHide = true
    end
    if shouldHide then
        tooltip:setVisible(false)
    else
        tooltip:setVisible(reason)
    end
end

local function behavioronFocusChange()
    for _, field in pairs(UITextEdit) do
        field.onFocusChange = onFocusChange
    end
end

local function behavioronTextChange()
    UITextEdit.email.onTextChange = function(widget, text)
        widget:setColor("#FFFFFF")
        if #text > 0 then
            local localRes = CreateAccountWeb.validateEmailLocally(text)
            setRequirementState(iconsCreateAccount.Email, localRes.IsValid, widget, localRes.errorMessage)
            widget:setColor(localRes.IsValid and "#76EE00" or "#EC644B")
        else
            setRequirementState(iconsCreateAccount.Email, false, widget, false)
        end
        handleTextChange(widget, "email", checkEmail)
    end

    UITextEdit.password.onTextChange = function(widget, text)
        widget:setColor("#FFFFFF")
        if #text > 0 then
            local localRes = CreateAccountWeb.validatePasswordLocally(text)
            updatePasswordRequirements(localRes)
            widget:setColor(localRes.PasswordValid and "#76EE00" or "#EC644B")
        else
            setRequirementState(iconsCreateAccount.Password, false)
            local reqPanel = toolstips.password
            if reqPanel then
                for _, child in ipairs(reqPanel:getChildren()) do
                    local icon = child:getChildById('icons')
                    if icon then
                        icon:disable()
                    end
                end
            end
        end
        toolstips.password:setVisible(#text ~= 0)

        handleTextChange(widget, "password", checkPassword)

        local repeatPassword = UITextEdit.repeatPassword:getText()
        if #repeatPassword > 0 then
            setRequirementState(iconsCreateAccount.RepeatPassword, repeatPassword == text)
            UITextEdit.repeatPassword:setColor(repeatPassword == text and "#76EE00" or "#EC644B")
        end
    end

    UITextEdit.repeatPassword.onTextChange = function(widget, text)
        local password = UITextEdit.password:getText()
        if #password == 0 then
            widget:setColor("#FFFFFF")
            return
        end
        local passwordRepeat = widget:getText()
        if #passwordRepeat == 0 then
            toolstips.allExceptPassword:getChildById("textEditRepeatPassword"):setVisible(false)
            return
        end
        local matches = text == password
        widget:setColor(matches and "#76EE00" or "#EC644B")
        setRequirementState(iconsCreateAccount.RepeatPassword, matches, widget, "The two passwords do not match!")
    end

    UITextEdit.character.onTextChange = function(widget, text)
        local filteredText = text:gsub("[^a-zA-Z ]", "")
        if filteredText ~= text then
            widget:setText(filteredText)
            return
        end
        widget:setColor("#FFFFFF")
        if #filteredText > 0 then
            local localRes = CreateAccountWeb.validateCharacterNameLocally(filteredText)
            setRequirementState(iconsCreateCharacter.CharacterName, localRes.IsAvailable, widget, localRes.errorMessage)
            widget:setColor(localRes.IsAvailable and "#76EE00" or "#EC644B")
        else
            setRequirementState(iconsCreateCharacter.CharacterName, false, widget, false)
        end
        handleTextChange(widget, "character", checkCharacterName)
    end
end

local function behavioronCheckChange()
    MainWindowsCreateAccount.createAccount.createYourAccount.panelCheckBox.checkboxPrivacy.onCheckChange =
        function(a, b)
            setRequirementState(a:getParent():getChildById('icons'), b)
        end
end

-- /*=============================================
-- =            onClick - Create Your Account    =
-- =============================================*/

function toggleCreateAccount(bool)
    if bool then
        EnterGame.show()
        destroyCreateAccount()
    else
        EnterGame.hide()
        createWidgetAccount()
    end
end

function onClickStartPlaying()
    local uiElements = {UITextEdit.email, UITextEdit.password, UITextEdit.character, UITextEdit.repeatPassword}
    for _, element in ipairs(uiElements) do
        element:disable()
    end

    globalInfo.email = UITextEdit.email:getText()
    globalInfo.password = UITextEdit.password:getText()
    globalInfo.characterName = UITextEdit.character:getText()
    globalInfo.characterSex = sexModeGroup:getSelectedWidget():getText():lower()

    createAccountAndCharacter(globalInfo, function(data, err)
        for _, element in ipairs(uiElements) do
            element:enable()
        end

        if err or not data then
            displayErrorBox(tr('Account Creation Error'), err or tr('Could not connect to account creation service.'))
            return
        end

        local isSuccess = data.Success or data.success
        if isSuccess then
            local account = g_crypt.encrypt(globalInfo.email)
            local password = g_crypt.encrypt(globalInfo.password)
            EnterGame.setAccountName(account)
            EnterGame.setPassword(password)
            destroyCreateAccount()
            EnterGame.doLogin()
        else
            local errorMsg = data.errorMessage or data.message or tr('Could not create account and character.')
            displayErrorBox(tr('Account Creation Failed'), errorMsg)
        end
    end)
end

function onClickSuggestName()
    generateCharacterName(function(data, err)
        if err or not data then
            reportRequestWarning("generatecharactername", err, "fx onClickSuggestName")
            return
        end
        UITextEdit.character:setText(data.GeneratedName)
    end)
end

-- /*=============================================
-- =    Panel game world to play on   =
-- =============================================*/
dofile('create_account/worlds')

local CreateAccountWorldsHandlers = nil
local function initWorlds()
    CreateAccountWorldsHandlers = CreateAccountWorlds.init({
        Worlds = Worlds,
        UIComboBox = UIComboBox,
        UITextList = UITextList,
        UIlabel = UIlabel,
        globalInfo = globalInfo,
        auxWidgets = auxWidgets
    })
end

local function findWorldByName(name)
    return CreateAccountWorldsHandlers.findWorldByName(name)
end

local function initializeWorldsList(worlds)
    CreateAccountWorldsHandlers.initializeWorldsList(worlds)
end

local function updateWorldInformation(widget)
    CreateAccountWorldsHandlers.updateWorldInformation(widget)
end

-- /*=============================================
-- =    OnClick Select a game world to play on   =
-- =============================================*/

function onClickResetGameWorld()
    if auxWidgets.worldDefault then
        UITextList.listAllWorlds:focusChild(auxWidgets.worldDefault, KeyboardFocusReason)
        UITextList.listAllWorlds:ensureChildVisible(auxWidgets.worldDefault)
    end
end

function toggleMainPanels(bool)
    MainWindowsCreateAccount.createAccount:setVisible(not bool)
    MainWindowsCreateAccount.mainPanelSelectAGameWorldToPlayOn:setVisible(bool)
    if bool then
        MainWindowsCreateAccount:setHeight(350)
    else
        MainWindowsCreateAccount:setHeight(390)
    end
end

function onClickOkChangeWorld()
    toggleMainPanels(false)
    globalInfo.selectedWorld = UIlabel.titleMiniPanelWorld:getText()
    UIlabel.RecommendedWorld:setText(globalInfo.selectedWorld)
    UIlabel.RecommendedWorld:setText(string.format("%s (%s)", globalInfo.selectedWorld,
        findWorldByName(globalInfo.selectedWorld).Region))
end

-- /*=============================================
-- =                    onInit                   =
-- =============================================*/

function createWidgetAccount()
    if not MainWindowsCreateAccount then
        getAccountCreationStatus(function(data, err)
            ensableBtnCreateNewAccount()
            if err or not data then
                reportRequestWarning("getaccountcreationstatus", err, "fx createWidgetAccount")
                return
            end
            MainWindowsCreateAccount = g_ui.displayUI('createAccount')
            -- LuaFormatter off
            UIwidgetImagen.AccountData = MainWindowsCreateAccount.imagesBanner.accountdatainvalid
            UIwidgetImagen.AllData = MainWindowsCreateAccount.imagesBanner.banneralldatainvalid
            UIwidgetImagen.CharacterData = MainWindowsCreateAccount.imagesBanner.bannercharacterdatainvalid

            UIlabel.RecommendedWorld = MainWindowsCreateAccount.createAccount.createYourCharacter.panelRecommendedWorld.worldLabel

            sexModeGroup = UIRadioGroup.create()
            sexModeGroup:addWidget(MainWindowsCreateAccount.createAccount.createYourCharacter.panelSex.Male)
            sexModeGroup:addWidget(MainWindowsCreateAccount.createAccount.createYourCharacter.panelSex.Female)
            -- sexModeGroup.onSelectionChange = sexModeChange
            sexModeGroup:selectWidget(MainWindowsCreateAccount.createAccount.createYourCharacter.panelSex.Male)

            -- world
            UIComboBox.world = MainWindowsCreateAccount.mainPanelSelectAGameWorldToPlayOn.panelSelectAGameWorldToPlayOn.panelSelectworldAndPvp.comboBoxWorld
            UIComboBox.pvp = MainWindowsCreateAccount.mainPanelSelectAGameWorldToPlayOn.panelSelectAGameWorldToPlayOn.panelSelectworldAndPvp.comboBoxPvp
            UITextList.listAllWorlds = MainWindowsCreateAccount.mainPanelSelectAGameWorldToPlayOn.panelSelectAGameWorldToPlayOn.textListAllWorlds
            UIlabel.titleMiniPanelWorld = MainWindowsCreateAccount.mainPanelSelectAGameWorldToPlayOn.panelSelectAGameWorldToPlayOn.worldInfo
    
            -- icons Account
            iconsCreateAccount.Password = MainWindowsCreateAccount.createAccount.createYourAccount.panelPassword.icons
            iconsCreateAccount.Email = MainWindowsCreateAccount.createAccount.createYourAccount.panelEmail.icons
            iconsCreateAccount.RepeatPassword = MainWindowsCreateAccount.createAccount.createYourAccount.panelRepeatPassword.icons
            iconsCreateAccount.CheckBox = MainWindowsCreateAccount.createAccount.createYourAccount.panelCheckBox.icons
            -- icons Characters
            iconsCreateCharacter.Sex = MainWindowsCreateAccount.createAccount.createYourCharacter.panelSex.icons
            iconsCreateCharacter.RecommendedWorld = MainWindowsCreateAccount.createAccount.createYourCharacter.panelRecommendedWorld.icons
            iconsCreateCharacter.CharacterName = MainWindowsCreateAccount.createAccount.createYourCharacter.panelCharacterName.icons

            -- Tooltips Password
            toolstips.allExceptPassword = MainWindowsCreateAccount.createAccount.testToolstips
            toolstips.password = MainWindowsCreateAccount.createAccount.passwordRequirements

            -- Input TextEdit
            UITextEdit.email = MainWindowsCreateAccount.createAccount.test.textEditEmail
            UITextEdit.password = MainWindowsCreateAccount.createAccount.test.textEditPassword
            UITextEdit.repeatPassword = MainWindowsCreateAccount.createAccount.test.textEditRepeatPassword
            UITextEdit.character = MainWindowsCreateAccount.createAccount.test.textEditCharacter
-- LuaFormatter on

            globalInfo.selectedWorld = data.RecommendedWorld

            initWorlds()
            initializeWorldsList(data.Worlds)
            UIlabel.RecommendedWorld:setText(string.format("%s (%s)", data.RecommendedWorld,
                findWorldByName(data.RecommendedWorld).Region))

            behavioronTextChange()
            behavioronFocusChange()
            behavioronCheckChange()
        end)
    else
        MainWindowsCreateAccount:show()
        ensableBtnCreateNewAccount()
    end
end

-- /*=============================================
-- =                    onTerminate              =
-- =============================================*/

function destroyCreateAccount()
    if MainWindowsCreateAccount then
        for _, widget in pairs(UIwidgetImagen or {}) do
            if widget and not widget:isDestroyed() then
                widget:destroy()
                widget = nil
            end
        end
        UIwidgetImagen = {}
        for _, widget in pairs(auxWidgets or {}) do
            if widget and not widget:isDestroyed() then
                widget:destroy()
                widget = nil
            end
        end
        auxWidgets = {}

        if sexModeGroup then
            sexModeGroup:destroy()
            sexModeGroup = nil
        end

        for _, widget in pairs(UIComboBox or {}) do
            if widget and not widget:isDestroyed() then
                widget:destroy()
                widget = nil
            end
        end
        UIComboBox = {}

        for _, widget in pairs(UITextEdit or {}) do
            if widget and not widget:isDestroyed() then
                widget:destroy()
                widget = nil
            end
        end
        UITextEdit = {}

        for _, widget in pairs(iconsCreateAccount or {}) do
            if widget and not widget:isDestroyed() then
                widget:destroy()
                widget = nil
            end
        end
        iconsCreateAccount = {}

        for _, widget in pairs(iconsCreateCharacter or {}) do
            if widget and not widget:isDestroyed() then
                widget:destroy()
                widget = nil
            end
        end
        iconsCreateCharacter = {}

        if UITextList.listAllWorlds then
            disconnect(UITextList.listAllWorlds, {
                onChildFocusChange = function(self, focusedChild)
                    if focusedChild == nil then
                        return
                    end
                    updateWorldInformation(focusedChild)
                end
            })
            UITextList.listAllWorlds:destroyChildren()
            UITextList.listAllWorlds:destroy()
            UITextList.listAllWorlds = nil
        end

        for _, widget in pairs(UIlabel or {}) do
            if widget and not widget:isDestroyed() then
                widget:destroy()
                widget = nil
            end
        end
        UIlabel = {}

        for _, widget in pairs(toolstips or {}) do
            if widget and not widget:isDestroyed() then
                widget:destroy()
            end
        end
        toolstips = {}

        if not MainWindowsCreateAccount:isDestroyed() then
            MainWindowsCreateAccount:destroy()
            MainWindowsCreateAccount = nil
        end
        Worlds = {}
        lastRequestTime = {}
    end
end
