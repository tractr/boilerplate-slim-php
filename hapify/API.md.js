
const DAY = 1000 * 60 * 60 * 24;

/**
 * Returns a default input value for a field
 *
 * @param {Object} f
 * @return {*}
 * @private
 */
function __defaultValue(f) {
    if (f.type === "boolean") return true;
    if (f.type === "string") {
        if (f.subtype === "email") return `${f.names.snake.replace(/_/g, '.')}@example.com`;
        if (f.subtype === "password") return __randomString();
        return `${f.names.capital}`;
    }
    if (f.type === "number") {
        if (f.subtype === "latitude") return __randomLatitude();
        if (f.subtype === "longitude") return __randomLongitude();
        return __randomNumber();
    }
    if (f.type === "datetime") return Date.now();
    if (f.type === "entity") return f.multiple ? [__randomId()] : __randomId();
    if (f.type === "object") return { foo: 'bar' };
    return null;
}
/**
 * Returns a default input update value for a field
 *
 * @param {Object} f
 * @return {*}
 * @private
 */
function __defaultUpdatedValue(f) {
    if (f.type === "boolean") return false;
    if (f.type === "string") {
        if (f.subtype === "email") return `new.${f.names.snake.replace(/_/g, '.')}@example.com`;
        if (f.subtype === "password") return __randomString();
        return `New ${f.names.capital}`;
    }
    if (f.type === "number") {
        if (f.subtype === "latitude") return __randomLatitude();
        if (f.subtype === "longitude") return __randomLongitude();
        return __randomNumber(11);
    }
    if (f.type === "datetime") return Date.now() + __randomNumber(5) * DAY;
    if (f.type === "entity") return f.multiple ? [__randomId()] : __randomId();
    if (f.type === "object") return { foo: 'foo' };
    return null;
}
/**
 * Returns a default input search type for a field
 *
 * @param {Object} f
 * @return {*}
 * @private
 */
function __searchType(f) {
    if (f.type === "boolean") return "boolean";
    if (f.type === "string") return "string";
    if (f.type === "number") return "number";
    if (f.type === "datetime") return "number";
    if (f.type === "entity") return "string";
    return "null";
}
/**
 * Returns a default output value for a field
 *
 * @param {Object} f
 * @param {Boolean} deep
 *  Get referenced entity ?
 * @return {*}
 * @private
 */
function __defaultOutputValue(f, deep) {
    if (f.primary) return __randomId();
    if (f.type === "boolean") return true;
    if (f.type === "string") {
        if (f.subtype === "email") return `${f.names.snake.replace(/_/g, '.')}@example.com`;
        if (f.subtype === "password") return __randomString();
        return `${f.names.capital}`;
    }
    if (f.type === "number") {
        if (f.subtype === "latitude") return __randomLatitude();
        if (f.subtype === "longitude") return __randomLongitude();
        return __randomNumber();
    }
    if (f.type === "datetime") return Date.now();
    if (f.type === "entity") {
        return f.multiple ?
            (deep ? [__output(f.model, 0)] : [__randomId()]) :
            (deep ? __output(f.model, 0) : __randomId());
    }
    if (f.type === "object") return { foo: 'bar' };
    return null;
}
/**
 * Convert an object to json with indent
 *
 * @param {Object} object
 * @return {String}
 * @private
 */
function __objectToJson(object) {
    return '```json\n'+JSON.stringify(object, null, 4)+'\n```';
}
/**
 * Generate a random Id
 *
 * @returns {Number}
 * @private
 */
function __randomId () {
    return __randomNumber(1, 1000);
}
/**
 * Generate a random string
 *
 * @returns {String}
 * @private
 */
function __randomString () {
    let text = "";
    const possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    for (var i = 0; i < 12; i++) {
        text += possible.charAt(Math.floor(Math.random() * possible.length));
    }
    return text;
}
/**
 * Generate a random number
 *
 * @param {Number} min
 * @param {Number} max
 * @returns {Number}
 * @private
 */
function __randomNumber (min = 0, max = 10) {
    return Math.floor(Math.random()*(max-min)) + min;
}
/**
 * Generate a random latitude
 *
 * @returns {Number}
 * @private
 */
function __randomLatitude () {
    return (Math.floor(Math.random()*180000) - 90000) / 1000;
}
/**
 * Generate a random longitude
 *
 * @returns {Number}
 * @private
 */
function __randomLongitude () {
    return (Math.floor(Math.random()*360000) - 180000) / 1000;
}
/**
 * Generate the intro
 *
 * @param {Object} models
 * @return {String}
 * @private
 */
function __intro(models) {

    const modelsNames = models.map((m) => m.names.capital).join('`, `');

    return `
# Slim 4 Boilerplate

Slim 4 starter kit API. Describes endpoints for plugins \`session\` and for models \`${modelsNames}\`.\n\n`;
}

/**
 * Generate an error 404
 *
 * @param {Object} model
 * @return {String}
 * @private
 */
function __error404(model) {

    const payload = __objectToJson({
        statusCode: 404,
        error: "Not found",
        message: `${model.names.capital} not found`
    });
    let output = `**Response 404** *(application/json)*\n\n`;
    output += `No ${model.names.lower} with this id was found.\n\n`;
    output += `${payload}\n\n`;

    return output;
}
/**
 * Generate an error 409
 *
 * @param {Object} model
 * @return {String}
 * @private
 */
function __error409(model) {

    const payload = __objectToJson({
        statusCode: 409,
        error: "Conflict",
        message: "Duplicate key"
    });
    let output = `**Response 409** *(application/json)*\n\n`;
    output += `Another ${model.names.lower} with this unique key exists.\n\n`;
    output += `${payload}\n\n`;

    return output;
}
/**
 * Generate an object to simulate model output
 *
 * @param {Object} model
 * @param {Number} depth
 *  0 => none
 *  1 => embbeded
 *  2 => all
 * @return {Object}
 * @private
 */
function __output(model, depth) {

    const output = {};
    model.fields.list.forEach((f) => {
        if (f.hidden) return;
        const deep = depth === 2 || (depth === 1 && f.embedded);
        output[f.names.snake] = __defaultOutputValue(f, deep);
    });

    return output;
}
/**
 * Generate a create doc
 *
 * @param {Object} model
 * @return {String}
 * @private
 */
function __create(model) {

    const inPayload = model.fields.list.reduce((p, f) => {
        if (f.internal) return p;
        p[f.names.snake] = __defaultValue(f);
        return p;
    }, {});
    const input = __objectToJson(inPayload);

    const outPayload = __output(model, 0);
    const output = __objectToJson(outPayload);

    let out = `### Create \`POST /${model.names.kebab}\`\n\n`;
    out += "**Request** *(application/json)*\n\n";
    out += `${input}\n\n`;
    out += "**Response 201** *(application/json)*\n\n";
    out += `${output}\n\n`;
    if (model.fields.unique.length) {
        out += __error409(model);
    }

    return out;
}
/**
 * Generate a read doc
 *
 * @param {Object} model
 * @return {String}
 * @private
 */
function __read(model) {

    const outPayload = __output(model, 2);
    const output = __objectToJson(outPayload);

    let out = `### Read \`GET /${model.names.kebab}/{${model.names.snake}_id}\`\n\n`;
    out += "**Parameters**\n\n";
    out += `+ \`${model.names.snake}_id\` *(number)* - The id of the ${model.names.lower}.\n\n`;
    out += "**Request** *(application/json)*\n\n";
    out += "**Response 200** *(application/json)*\n\n";
    out += `${output}\n\n`;
    out += __error404(model);

    return out;
}

/**
 * Generate a delete doc
 *
 * @param {Object} model
 * @return {String}
 * @private
 */
function __delete(model) {

    let out = `### Delete \`DELETE /${model.names.kebab}/{${model.names.snake}_id}\`\n\n`;
    if (model.referencedIn && model.referencedIn.length) {
        const props = [];
        model.referencedIn.forEach((m) => {
            m.fields.forEach((f) => {
                props.push(`${m.names.pascal}.${f.names.snake}`);
            });
        });
        const list = `\`${props.join('`, `')}\``;
        out += `This action will also remove references from ${list}.\n\n`;
    }
    out += "**Parameters**\n\n";
    out += `+ \`${model.names.snake}_id\` *(number)* - The id of the ${model.names.lower}.\n\n`;
    out += "**Request** *(application/json)*\n\n";
    out += "**Response 204** *(application/json)*\n\n";
    out += __error404(model);

    return out;
}

/**
 * Generate an update doc
 *
 * @param {Object} model
 * @return {String}
 * @private
 */
function __update(model) {

    const inPayload = model.fields.list.reduce((p, f) => {
        if (f.internal) return p;
        p[f.names.snake] = __defaultUpdatedValue(f);
        return p;
    }, {});
    const input = __objectToJson(inPayload);

    let out = `### Update \`PATCH /${model.names.kebab}/{${model.names.snake}_id}\`\n\n`;
    out += "**Parameters**\n\n";
    out += `+ \`${model.names.snake}_id\` *(number)* - The id of the ${model.names.lower}.\n\n`;
    out += "**Request** *(application/json)*\n\n";
    out += `${input}\n\n`;
    out += "**Response 204** *(application/json)*\n\n";
    out += __error404(model);
    if (model.fields.unique.length) {
        out += __error409(model);
    }

    return out;
}
/**
 * Generate the query part of a search
 *
 * @param model
 * @return {Array}
 * @private
 */
function __search_query(model) {

    const query = [];
    model.fields.searchable.forEach((f) => {
        let description = '';
        if (f.type === 'entity') {
            description = `Id of ${f.names.lower}.`;
            if (f.multiple) description = `${description} Can be multiple.`;
        } else {
            description = `Value for ${f.names.lower}.`;
        }
        query.push({
            key: f.names.snake,
            type:  __searchType(f),
            required: false,
            description
        });
        if (f.type === 'number') {
            query.push({
                key: `${f.names.snake}__min`,
                type:  'number',
                required: false,
                description: `Minimum value for ${f.names.lower}.`
            });
            query.push({
                key: `${f.names.snake}__max`,
                type:  'number',
                required: false,
                description: `Maximum value for ${f.names.lower}.`
            });
        }
        else if (f.type === 'datetime') {
            query.push({
                key: `${f.names.snake}__min`,
                type:  'number',
                required: false,
                description: `Minimum value for ${f.names.lower}.`
            });
            query.push({
                key: `${f.names.snake}__max`,
                type:  'number',
                required: false,
                description: `Maximum value for ${f.names.lower}.`
            });
        }
    });
    return query;
}
/**
 * Generate a list doc
 *
 * @param {Object} model
 * @return {String}
 * @private
 */
function __list(model) {

    const query = __search_query(model);

    const pagination = [
        {
            key: "_page",
            type:  'number',
            required: true,
            description: "Page number. Greater or equal than 0."
        },
        {
            key: "_limit",
            type:  'number',
            required: true,
            description: "Required results length. Greater or equal than 1. Lower or equal than 100."
        }
    ];

    const sortables = model.fields.sortable.map((f) => f.names.snake).join('`, `');
    const sorter = model.fields.sortable.length ? [
        {
            key: "_sort",
            type:  'string',
            required: false,
            description: `Required sort. Can be \`${sortables}\`.`
        },
        {
            key: "_order",
            type:  'string',
            required: false,
            description: "Required sort order. Can be `asc` or `desc`."
        }
    ] : [];

    const params = query.concat(pagination).concat(sorter);
    const paramsList = params.map(p => p.key).join(',');

    const outPayload = __output(model, 1);
    const output = __objectToJson({
        page: 0,
        limit: 10,
        count: 10,
        total: 32,
        items: [outPayload]
    });

    let out = `### List \`GET /${model.names.kebab}{?${paramsList}}\`\n\n`;
    out += "**Parameters**\n\n";
    params.forEach((p) => {
        out += `+ \`${p.key}\` *(${p.required ? '' : 'optional, '}${p.type})* - ${p.description}\n`;
    });
    out += "\n";
    out += "**Request** *(application/json)*\n\n";
    out += "**Response 200** *(application/json)*\n\n";
    out += `${output}\n\n`;

    return out;
}
/**
 * Generate a count doc
 *
 * @param {Object} model
 * @return {String}
 * @private
 */
function __count(model) {

    const query = __search_query(model);
    const params = query;
    const paramsList = params.map(p => p.key).join(',');
    const output = __objectToJson({ total: 32 });

    let out = `### Count \`GET /${model.names.kebab}/count{?${paramsList}}\`\n\n`;
    out += "**Parameters**\n\n";
    params.forEach((p) => {
        out += `+ \`${p.key}\` *(${p.required ? '' : 'optional, '}${p.type})* - ${p.description}\n`;
    });
    out += "\n";
    out += "**Request** *(application/json)*\n\n";
    out += "**Response 200** *(application/json)*\n\n";
    out += `${output}\n\n`;

    return out;
}

/**
 * Generate a model
 *
 * @param {Object} model
 * @return {String}
 * @private
 */
function __model(model) {

    let out = `## ${model.names.capital}\n\n`;
    out += __create(model);
    out += __read(model);
    out += __update(model);
    out += __delete(model);
    out += __list(model);
    out += __count(model);

    return out;
}

/**
 * Returns static routes doc
 *
 * @return {String}
 * @private
 */
function __static() {
    return `## Session

### Login \`POST /session\`

**Request** *(application/json)*

\`\`\`json
{
    "email": "john@mail.com",
    "password": "pAssW0rd"
}
\`\`\`

**Response 201** *(application/json)*

Login sucessful. An authentication cookie is also returned.

\`\`\`json
{
    "_id": 12,
    "name": "John Doe",
    "email": "john@mail.com",
    "role": "user"
}
\`\`\`

**Response 404** *(application/json)*

Wrong login or wrong password.

\`\`\`json
{
    "statusCode": 404,
    "error": "Not Found",
    "message": "User not found or wrong password"
}
\`\`\`

### Logout \`DELETE /session\`

**Request** *(application/json)*

**Response 204** *(application/json)*

**Response 401** *(application/json)*

Missing or wrong cookie.

\`\`\`json
{
    "statusCode": 401,
    "error": "Unauthorized",
    "message": "Invalid cookie"
}
\`\`\`

### Current user \`GET /session\`

**Request** *(application/json)*

**Response 200** *(application/json)*

\`\`\`json
{
    "_id": 12,
    "name": "John Doe",
    "email": "john@mail.com",
    "role": "user"
}
\`\`\`

**Response 401** *(application/json)*

Missing or wrong cookie.

\`\`\`json
{
    "statusCode": 401,
    "error": "Unauthorized",
    "message": "Missing authentication"
}
\`\`\`
`;
}


let output = __intro(models);
output += models.map(__model).join("\n\n");
output += __static();

return output;
