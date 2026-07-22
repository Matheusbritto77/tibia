CreateAccountValidation = {}

function CreateAccountValidation.init(State)
    local function checkAllRequirements()
        local function allWidgetsEnabled(widgets)
            for _, widget in pairs(widgets) do
                if not widget:isEnabled() then
                    return false
                end
            end
            return true
        end
        local createAccountPassed = allWidgetsEnabled(State.iconsCreateAccount)
        local createYourCharacterPassed = allWidgetsEnabled(State.iconsCreateCharacter)
        State.UIwidgetImagen.AccountData:setEnabled(createAccountPassed)
        State.UIwidgetImagen.AllData:setEnabled(createAccountPassed and createYourCharacterPassed)
        State.UIwidgetImagen.CharacterData:setEnabled(createYourCharacterPassed)
        return createAccountPassed and createYourCharacterPassed
    end

    local function updateButtonState(button)
        button:setEnabled(checkAllRequirements())
    end

    local function setRequirementState(widget, enabled, widgetError, errorMessage)
        widget:setEnabled(enabled)
        updateButtonState(State.MainWindowsCreateAccount.createAccount.buttonStartPlaying)

        if widgetError then
            local errorWidget = State.toolstips.allExceptPassword:getChildById(widgetError:getId())
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
        local requirementsPanel = State.toolstips.password

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
        setRequirementState(State.iconsCreateAccount.Password, allRequirementsPassed)
    end

    return {
        checkAllRequirements = checkAllRequirements,
        updateButtonState = updateButtonState,
        setRequirementState = setRequirementState,
        updatePasswordRequirements = updatePasswordRequirements
    }
end
