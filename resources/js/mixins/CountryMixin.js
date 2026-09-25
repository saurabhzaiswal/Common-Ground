const priorityCountries = [
    'Australia',
    'India',
    'New Zealand',
    'China',
    'United States',
]

let countryModule
let stateModule
let cityModule

function getCountryApi() {
    countryModule ??= import('country-state-city/lib/country.js')

    return countryModule
}

function getStateApi() {
    stateModule ??= import('country-state-city/lib/state.js')

    return stateModule
}

function getCityApi() {
    cityModule ??= import('country-state-city/lib/city.js')

    return cityModule
}

function toLocationOption(item) {
    return {
        label: item.name,
        value: item.name,
        code: item.isoCode,
    }
}

function toCustomOption(value) {
    const name = String(value).trim()

    return { label: name, value: name, code: null }
}

export default {
    data() {
        return {
            allCountry: [],
            countryOptions: [],
            stateOptions: [],
            cityOptions: [],
            countryDataReady: false,
        }
    },
    async created() {
        await this.sortCountries()
    },
    methods: {
        async sortCountries() {
            try {
                const { default: Country } = await getCountryApi()
                const allCountries = Country.getAllCountries()
                const priorityCountrySet = new Set(priorityCountries)
                const sortedCountries = allCountries
                    .filter((country) => priorityCountrySet.has(country.name))
                    .sort((first, second) => priorityCountries.indexOf(first.name) - priorityCountries.indexOf(second.name))
                    .concat(
                        allCountries
                            .filter((country) => !priorityCountrySet.has(country.name))
                            .sort((first, second) => first.name.localeCompare(second.name)),
                    )

                this.allCountry = sortedCountries
                this.countryOptions = sortedCountries.map(toLocationOption)
            } catch (error) {
                this.countryOptions = []
            } finally {
                this.countryDataReady = true
            }
        },
        async getAllState(countryName) {
            this.stateOptions = []
            this.cityOptions = []

            if (!this.countryDataReady) {
                await this.sortCountries()
            }

            const country = this.findCountry(countryName)
            if (!country?.isoCode) {
                this.addCurrentLocationOption('stateOptions', this.form.state)
                return
            }

            try {
                const { default: State } = await getStateApi()
                this.stateOptions = State.getStatesOfCountry(country.isoCode)
                    .map(toLocationOption)
                    .sort((first, second) => first.label.localeCompare(second.label))
                this.addCurrentLocationOption('stateOptions', this.form.state)
            } catch (error) {
                this.stateOptions = []
                this.addCurrentLocationOption('stateOptions', this.form.state)
            }
        },
        async getAllCity(countryName, stateName) {
            this.cityOptions = []

            if (!this.countryDataReady) {
                await this.sortCountries()
            }

            const country = this.findCountry(countryName)
            if (!country?.isoCode || !stateName) {
                this.addCurrentLocationOption('cityOptions', this.form.city)
                return
            }

            try {
                const [{ default: City }, { default: State }] = await Promise.all([
                    getCityApi(),
                    getStateApi(),
                ])
                const state = State.getStatesOfCountry(country.isoCode)
                    .find((item) => item.name.toLocaleLowerCase() === String(stateName).toLocaleLowerCase())

                if (!state?.isoCode) {
                    this.addCurrentLocationOption('cityOptions', this.form.city)
                    return
                }

                this.cityOptions = City.getCitiesOfState(country.isoCode, state.isoCode)
                    .map(toLocationOption)
                    .sort((first, second) => first.label.localeCompare(second.label))
                this.addCurrentLocationOption('cityOptions', this.form.city)
            } catch (error) {
                this.cityOptions = []
                this.addCurrentLocationOption('cityOptions', this.form.city)
            }
        },
        findCountry(countryName) {
            const searchName = String(countryName || '').trim().toLocaleLowerCase()

            return this.allCountry.find((country) => (
                country.name.toLocaleLowerCase() === searchName
                || country.isoCode.toLocaleLowerCase() === searchName
            ))
        },
        addCurrentLocationOption(optionsName, locationName) {
            const name = String(locationName || '').trim()
            if (!name || this[optionsName].some((option) => option.value.toLocaleLowerCase() === name.toLocaleLowerCase())) {
                return
            }

            this[optionsName].push(toCustomOption(name))
        },
        createCountryOption(value) {
            return toCustomOption(value)
        },
        createStateOption(value) {
            return toCustomOption(value)
        },
        createCityOption(value) {
            return toCustomOption(value)
        },
    },
}
