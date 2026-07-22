CreateAccountWorlds = {}

function CreateAccountWorlds.init(State)
    local function findWorldByName(name)
        for _, world in pairs(State.Worlds) do
            if world.Name:lower() == name:lower() then
                return world
            end
        end
        return nil
    end

    local function filterWorldsList()
        local selectedRegion = State.UIComboBox.world:getCurrentOption().text
        local selectedPvpType = State.UIComboBox.pvp:getCurrentOption().text

        local index = 0
        for _, widget in pairs(State.UITextList.listAllWorlds:getChildren()) do
            local world = findWorldByName(widget:getId())
            local regionMatch = selectedRegion == "All" or world.Region == selectedRegion
            local pvpMatch = selectedPvpType == "All" or world.PvPType == selectedPvpType

            local visible = regionMatch and pvpMatch
            widget:setVisible(visible)
            if visible then
                index = index + 1
                widget:setBackgroundColor(index % 2 == 0 and "#ffffff12" or "#00000012")
            end
        end
    end

    local function updateWorldInformation(widget)
        local world = findWorldByName(widget:getId())

        State.UIlabel.titleMiniPanelWorld:setText(world.Name)
        for key, value in pairs(world) do
            local w = State.UIlabel.titleMiniPanelWorld:recursiveGetChildById(key)
            if w then
                if key == "CreationDate" then
                    w:setText(os.date("%b. %Y", value))
                elseif key == "PremiumOnly" then
                    w:setText(tostring(value == 1))
                elseif key == "BattlEyeActivationTimestamp" then
                    local description =
                        world.BattlEyeInitiallyActive == 1 and "initially protected" or "protected since " ..
                            os.date("%b.%Y", value)
                    w:setText(description)
                else
                    w:setText(value)
                end
            end
        end
    end

    local function initializeWorldsList(worlds)
        local sortedWorlds = {}
        for _, world in pairs(worlds) do
            table.insert(sortedWorlds, world)
        end
        table.sort(sortedWorlds, function(a, b)
            return a.Name < b.Name
        end)

        State.UITextList.listAllWorlds:destroyChildren()
        State.Worlds = worlds

        local regions = { ["All"] = true }
        local pvpTypes = { ["All"] = true }
        local focusLabel
        for i, world in ipairs(sortedWorlds) do
            local widget = g_ui.createWidget('WorldWidget', State.UITextList.listAllWorlds)
            widget:setId(world.Name)
            widget:getChildById('details'):setText(world.Name)
            widget:setBackgroundColor(i % 2 == 0 and "#ffffff12" or "#00000012")
            if i == 1 then focusLabel = widget end
            if world.Name:lower() == State.globalInfo.selectedWorld:lower() then
                State.auxWidgets.worldDefault = widget
            end
            regions[world.Region] = true
            pvpTypes[world.PvPType] = true
        end
        if focusLabel then
            scheduleEvent(function()
                State.UITextList.listAllWorlds:focusChild(focusLabel, KeyboardFocusReason)
                State.UITextList.listAllWorlds:ensureChildVisible(focusLabel)
            end, 50)
        end
        connect(State.UITextList.listAllWorlds, {
            onChildFocusChange = function(self, focusedChild)
                if focusedChild then updateWorldInformation(focusedChild) end
            end
        })
        State.UIComboBox.world:clearOptions()
        State.UIComboBox.world:addOption("All")
        for region in pairs(regions) do
            if region ~= "All" then State.UIComboBox.world:addOption(region) end
        end
        State.UIComboBox.pvp:clearOptions()
        State.UIComboBox.pvp:addOption("All")
        for pvpType in pairs(pvpTypes) do
            if pvpType ~= "All" then State.UIComboBox.pvp:addOption(pvpType) end
        end
        State.UIComboBox.world.onOptionChange = filterWorldsList
        State.UIComboBox.pvp.onOptionChange = filterWorldsList
    end

    return {
        findWorldByName = findWorldByName,
        initializeWorldsList = initializeWorldsList,
        updateWorldInformation = updateWorldInformation
    }
end
